<?php

namespace Bitpin\Api\Services;

use Bitpin\Api\Contracts\BitpinClientInterface;
use Bitpin\Api\DTOs\{AuthTokens,
    AccessToken,
    Currency,
    MarketData,
    Network,
    NetworkWithMaintenance,
    OrderBook,
    Ticker,
    TradeMatch,
    Candle,
    Commission,
    Order,
    OrderRequest};
use Bitpin\Api\Exceptions\BitpinApiException;
use Bitpin\Api\Exceptions\TooManyRequestsException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\{GuzzleException, RequestException, ConnectException, ServerException, ClientException};
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Log;

class BitpinClient implements BitpinClientInterface
{
    protected Client $httpClient;
    protected array $config;
    protected ?string $accessToken = null;
    protected ?string $refreshToken = null;
    protected ?int $tokenExpiry = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->httpClient = new Client([
            'base_uri' => rtrim($config['base_url'], '/') . '/',
            'timeout' => $config['timeout'],
            'connect_timeout' => $config['connect_timeout'],
            'headers' => $config['default_headers'],
            'http_errors' => false, // We handle errors manually
        ]);
    }

    // ==========================================
    // Authentication
    // ==========================================

    /**
     * @throws BitpinApiException
     */
    public function authenticate(string $apiKey, string $secretKey): AuthTokens
    {
        $response = $this->makeRequest('POST', '/api/v1/usr/authenticate/', [
            'json' => [
                'api_key' => $apiKey,
                'secret_key' => $secretKey,
            ],
        ]);

        $tokens = AuthTokens::fromArray($response);
        $this->setTokens($tokens->access, $tokens->refresh);

        return $tokens;
    }

    /**
     * @throws BitpinApiException
     */
    public function refreshAccessToken(string $refreshToken): AccessToken
    {
        $response = $this->makeRequest('POST', '/api/v1/usr/refresh_token/', [
            'json' => ['refresh' => $refreshToken],
        ]);

        $token = AccessToken::fromArray($response);
        $this->setAccessToken($token->access);

        return $token;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
        // Decode JWT to get expiry (optional, for auto-refresh)
        $this->tokenExpiry = $this->extractTokenExpiry($accessToken);
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function isAuthenticated(): bool
    {
        return !empty($this->accessToken);
    }

    protected function setTokens(string $accessToken, string $refreshToken): void
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->tokenExpiry = $this->extractTokenExpiry($accessToken);
    }

    protected function extractTokenExpiry(string $token): ?int
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }
            $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
            return $payload['exp'] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function shouldRefreshToken(): bool
    {
        if (!$this->tokenExpiry || !$this->refreshToken) {
            return false;
        }
        $buffer = $this->config['token_refresh_buffer'] ?? 60;
        return (time() + $buffer) >= $this->tokenExpiry;
    }

    // ==========================================
    // Market Data - Currencies
    // ==========================================

    /**
     * @throws BitpinApiException
     */
    public function getCurrencies(): array
    {
        $data = $this->makeRequest('GET', '/api/v1/mkt/currencies/');
        return array_map(fn($item) => Currency::fromArray($item), $data);
    }

    // ==========================================
    // Market Data - Markets
    // ==========================================

    /**
     * @throws BitpinApiException
     */
    public function getMarkets(): array
    {
        $data = $this->makeRequest('GET', '/api/v1/mkt/markets/');
        return array_map(fn($item) => MarketData::fromArray($item), $data);
    }

    // ==========================================
    // Market Data - Networks
    // ==========================================

    /**
     * @throws BitpinApiException
     */
    public function getNetworks(?string $code = null, ?bool $canDeposit = null, ?bool $canWithdraw = null): array
    {
        $query = [];
        if ($code !== null) $query['code'] = $code;
        if ($canDeposit !== null) $query['can_deposit'] = $canDeposit ? 'true' : 'false';
        if ($canWithdraw !== null) $query['can_withdraw'] = $canWithdraw ? 'true' : 'false';

        $data = $this->makeRequest('GET', '/api/v1/mkt/networks/', ['query' => $query]);
        return array_map(fn($item) => Network::fromArray($item), $data);
    }

    /**
     * @throws BitpinApiException
     */
    public function getNetworksForCurrency(string $currencyCode): array
    {
        $data = $this->makeRequest('GET', '/api/v2/wlt/networks/', [
            'query' => ['currency_code' => $currencyCode],
            'auth' => true,
        ]);
        return array_map(fn($item) => NetworkWithMaintenance::fromArray($item), $data);
    }

    // ==========================================
    // Market Data - Tickers
    // ==========================================

    /**
     * @throws BitpinApiException
     */
    public function getTickers(): array
    {
        $data = $this->makeRequest('GET', '/api/v1/mkt/tickers/');
        return array_map(fn($item) => Ticker::fromArray($item), $data);
    }

    // ==========================================
    // Market Data - Order Book
    // ==========================================

    /**
     * @throws BitpinApiException|GuzzleException
     */
    public function getOrderBook(string $symbol): OrderBook
    {
        $data = $this->makeRequest('GET', "/api/v1/mth/orderbook/{$symbol}/");
        return OrderBook::fromArray($data);
    }

    // ==========================================
    // Market Data - Matches (Recent Trades)
    // ==========================================

    /**
     * @throws BitpinApiException
     */
    public function getMatches(string $symbol): array
    {
        $data = $this->makeRequest('GET', "/api/v1/mth/matches/{$symbol}/");
        return array_map(fn($item) => TradeMatch::fromArray($item), $data);
    }

    // ==========================================
    // Market Data - Candles (OHLCV)
    // ==========================================

    /**
     * @throws BitpinApiException
     */
    public function getCandles(string $symbol, int $from, int $to, string $resolution = '30m'): array
    {
        $data = $this->makeRequest('GET', '/api/v1/mkt/candles/', [
            'query' => [
                'symbol' => $symbol,
                'from' => $from,
                'to' => $to,
                'res' => $resolution,
            ],
        ]);
        return array_map(fn($item) => Candle::fromArray($item), $data);
    }

    // ==========================================
    // Market Data - Commissions
    // ==========================================

    /**
     * @throws BitpinApiException
     */
    public function getCommissions(): array
    {
        $data = $this->makeRequest('GET', '/api/v1/mkt/commissions/', ['auth' => true]);
        return array_map(fn($item) => Commission::fromArray($item), $data);
    }

    // ==========================================
    // Orders
    // ==========================================

    /**
     * @throws BitpinApiException
     */
    public function createOrder(OrderRequest $request): Order
    {
        $data = $this->makeRequest('POST', '/api/v1/odr/orders', [
            'json' => $request->toArray(),
            'auth' => true,
        ]);
        return Order::fromArray($data);
    }

    /**
     * @throws BitpinApiException
     */
    public function getOrders(int $offset = 0, int $limit = 10): array
    {
        $data = $this->makeRequest('GET', '/v1/odr/orders', [
            'query' => ['offset' => $offset, 'limit' => min($limit, 100)],
            'auth' => true,
        ]);
        return array_map(fn($item) => Order::fromArray($item), $data);
    }

    // ==========================================
    // WebSocket
    // ==========================================

    public function getWebSocketUrl(): string
    {
        return $this->config['websocket_url'];
    }

    // ==========================================
    // HTTP Request Handling
    // ==========================================

    /**
     * @throws BitpinApiException
     * @throws GuzzleException
     */
    protected function makeRequest(
        string $method,
        string $uri,
        array $options = []
    ): array {
        $requiresAuth = $options['auth'] ?? false;

        if ($requiresAuth && !$this->isAuthenticated()) {
            throw BitpinApiException::authenticationRequired();
        }

        // Auto-refresh token if needed
        if ($requiresAuth && $this->config['auto_refresh_token'] && $this->shouldRefreshToken()) {
            $this->attemptTokenRefresh();
        }

        $requestOptions = $this->buildRequestOptions($method, $options);

        if ($this->config['logging']['enabled'] ?? false) {
            Log::channel($this->config['logging']['channel'])->log(
                $this->config['logging']['level'],
                "Bitpin API Request: {$method} {$uri}",
                ['options' => $requestOptions]
            );
        }

        $retries = $this->config['retries'] ?? 3;
        $retryDelay = $this->config['retry_delay'] ?? 1000;

        for ($attempt = 0; $attempt <= $retries; $attempt++) {
            try {
                $response = $this->httpClient->request($method, $uri, $requestOptions);
                $body = (string) $response->getBody();

                if ($this->config['logging']['enabled'] ?? false) {
                    Log::channel($this->config['logging']['channel'])->log(
                        $this->config['logging']['level'],
                        "Bitpin API Response: {$method} {$uri}",
                        ['status' => $response->getStatusCode(), 'body' => $body]
                    );
                }

                return $this->handleResponse($response, $body);
            } catch (TooManyRequestsException $e) {
                if ($attempt < $retries) {
                    $retryAfter = (int) ($e->getResponse()?->getHeaderLine('Retry-After') ?? ($retryDelay / 1000));
                    sleep($retryAfter);
                    continue;
                }
                throw BitpinApiException::rateLimited();
            } catch (ConnectException $e) {
                if ($attempt < $retries) {
                    usleep($retryDelay * 1000);
                    continue;
                }
                throw BitpinApiException::serverError('Connection failed: ' . $e->getMessage());
            } catch (ServerException $e) {
                if ($attempt < $retries) {
                    usleep($retryDelay * 1000);
                    continue;
                }
                throw BitpinApiException::serverError('Server error: ' . $e->getMessage());
            } catch (ClientException $e) {
                $response = $e->getResponse();
                $body = (string) $response->getBody();
                throw BitpinApiException::fromResponse($response, $body);
            } catch (RequestException $e) {
                if ($attempt < $retries) {
                    usleep($retryDelay * 1000);
                    continue;
                }
                throw BitpinApiException::serverError('Request failed: ' . $e->getMessage());
            }
        }

        throw BitpinApiException::serverError('Max retries exceeded');
    }

    protected function buildRequestOptions(string $method, array $options): array
    {
        $requestOptions = [];

        if ($this->isAuthenticated()) {
            $requestOptions['headers']['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        if (isset($options['query'])) {
            $requestOptions['query'] = $options['query'];
        }

        if (isset($options['json'])) {
            $requestOptions['json'] = $options['json'];
        }

        if (isset($options['headers'])) {
            $requestOptions['headers'] = array_merge(
                $requestOptions['headers'] ?? [],
                $options['headers']
            );
        }

        return $requestOptions;
    }

    /**
     * @throws BitpinApiException
     */
    protected function handleResponse(ResponseInterface $response, string $body): array
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 200 && $statusCode < 300) {
            $data = json_decode($body, true);
            return is_array($data) ? $data : [];
        }

        // Handle specific error status codes
        switch ($statusCode) {
            case 401:
                // Check if token error
                $error = json_decode($body, true);
                if (str_contains((string) ($error['code'] ?? ''), 'token')) {
                    throw BitpinApiException::tokenExpired();
                }
                throw BitpinApiException::fromResponse($response, $body);

            case 403:
                throw BitpinApiException::ipNotAllowed();

            case 404:
                throw BitpinApiException::notFound();

            case 406:
                throw BitpinApiException::badRequest('Currency code is required');

            case 429:
                $retryAfter = (int) ($response->getHeaderLine('Retry-After') ?? 0);
                throw BitpinApiException::rateLimited($retryAfter);

            case 500:
            case 502:
            case 503:
            case 504:
                throw BitpinApiException::serverError();

            default:
                throw BitpinApiException::fromResponse($response, $body);
        }
    }

    protected function attemptTokenRefresh(): void
    {
        if (!$this->refreshToken) {
            return;
        }

        try {
            $this->refreshAccessToken($this->refreshToken);
        } catch (\Throwable) {
            // Token refresh failed, clear tokens
            $this->accessToken = null;
            $this->refreshToken = null;
            $this->tokenExpiry = null;
        }
    }
}