# Bitpin API PHP SDK

A comprehensive PHP SDK for the [Bitpin Exchange API](https://docs.bitpin.ir/v1), built with Laravel integration in mind. Uses Guzzle HTTP client for robust API communication.

## Features

- ✅ **Full REST API Coverage** - All documented endpoints from Bitpin API v1/v2
- ✅ **Laravel Integration** - Service provider, facade, and config publishing
- ✅ **Automatic Token Management** - Auto-refreshes expired access tokens
- ✅ **Typed DTOs** - All responses mapped to strongly-typed Data Transfer Objects
- ✅ **Error Handling** - Comprehensive exception hierarchy with HTTP status codes
- ✅ **Rate Limit Awareness** - Built-in retry logic with exponential backoff
- ✅ **Logging Support** - Optional request/response logging for debugging
- ✅ **WebSocket Support** - Helper for Centrifugo WebSocket connections
- ✅ **PHP 8.1+ Compatible** - Modern PHP with strict typing

## Installation

```bash
composer require zedxpm/bitpin-api-php
```

### Laravel Setup

The package auto-discovers the service provider. Optionally publish the config:

```bash
php artisan vendor:publish --tag=bitpin-config
```

Add your credentials to `.env`:

```env
BITPIN_API_KEY=your_api_key_here
BITPIN_SECRET_KEY=your_secret_key_here
BITPIN_BASE_URL=https://api.bitpin.org
BITPIN_WEBSOCKET_URL=wss://centrifugo.bitpin.org/connection/websocket
BITPIN_TIMEOUT=30
BITPIN_AUTO_REFRESH_TOKEN=true
BITPIN_LOGGING_ENABLED=false
```

## Quick Start

### Using the Facade (Laravel)

```php
use Bitpin\Api\Facades\Bitpin;

// Authenticate (gets access + refresh tokens)
$tokens = Bitpin::authenticate(
    config('bitpin.api_key'),
    config('bitpin.secret_key')
);

// Now make authenticated requests
$tickers = Bitpin::getTickers();
$markets = Bitpin::getMarkets();
$orderBook = Bitpin::getOrderBook('BTC_IRT');

// Create an order
$order = Bitpin::createOrder(
    \Bitpin\Api\DTOs\OrderRequest::limit('USDT_IRT', 'buy', 50000, 100)
);
```

### Using Dependency Injection

```php
use Bitpin\Api\Contracts\BitpinClientInterface;
use Bitpin\Api\DTOs\OrderRequest;

class TradingService
{
    public function __construct(
        protected BitpinClientInterface $bitpin
    ) {}

    public function placeLimitOrder(string $symbol, string $side, float $price, float $amount)
    {
        return $this->bitpin->createOrder(
            OrderRequest::limit($symbol, $side, $price, $amount)
        );
    }
}
```

### Standalone Usage (Non-Laravel)

```php
use Bitpin\Api\Services\BitpinClient;

$config = [
    'base_url' => 'https://api.bitpin.org',
    'api_key' => 'your_api_key',
    'secret_key' => 'your_secret_key',
    'timeout' => 30,
    'default_headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ],
    'auto_refresh_token' => true,
];

$client = new BitpinClient($config);
$tokens = $client->authenticate($config['api_key'], $config['secret_key']);

// Use the client...
$currencies = $client->getCurrencies();
```

## API Reference

### Authentication

| Method | Description |
|--------|-------------|
| `authenticate($apiKey, $secretKey)` | Exchange API key/secret for access + refresh tokens |
| `refreshAccessToken($refreshToken)` | Get new access token using refresh token |
| `setAccessToken($token)` | Manually set access token |
| `getAccessToken()` | Get current access token |
| `isAuthenticated()` | Check if authenticated |

### Market Data

```php
// Currencies
$currencies = Bitpin::getCurrencies();
// Returns: Currency[] with symbol, name, tradable, precision

// Markets
$markets = Bitpin::getMarkets();
// Returns: Market[] with symbol, base/quote, precisions

// Networks (v1)
$networks = Bitpin::getNetworks();
// Filter: Bitpin::getNetworks('TRX', true, false)

// Networks for specific currency (v2, requires auth)
$networks = Bitpin::getNetworksForCurrency('USDT');
// Returns: NetworkWithMaintenance[] with maintenance status

// Tickers (current prices)
$tickers = Bitpin::getTickers();
// Returns: Ticker[] with price, daily_change, high, low, timestamp

// Order Book
$orderBook = Bitpin::getOrderBook('BTC_IRT');
// Returns: OrderBook with asks/bids arrays
$bestAsk = $orderBook->getBestAsk();
$bestBid = $orderBook->getBestBid();
$spread = $orderBook->getSpread();

// Recent Matches (Trades)
$matches = Bitpin::getMatches('BTC_IRT');
// Returns: Match[] with price, amounts, side

// Candles (OHLCV)
$candles = Bitpin::getCandles('BTC_IRT', 1735689600, 1735776000, '1h');
// Valid resolutions: 1m, 5m, 15m, 30m, 1h, 3h, 4h, 6h, 1d, 1w, 1M
// Returns: Candle[] with open, high, low, close, volume

// Commissions (requires auth)
$commissions = Bitpin::getCommissions();
// Returns: Commission[] with maker/taker fees per market
```

### Orders

```php
use Bitpin\Api\DTOs\OrderRequest;

// Limit order
$order = Bitpin::createOrder(
    OrderRequest::limit('USDT_IRT', 'buy', 50000, 100)
);

// Market order
$order = Bitpin::createOrder(
    OrderRequest::market('USDT_IRT', 'sell', 50)
);

// Stop-limit order
$order = Bitpin::createOrder(
    OrderRequest::stopLimit('USDT_IRT', 'buy', 51000, 100, 50500)
);

// OCO (One-Cancels-Other) order
$order = Bitpin::createOrder(
    OrderRequest::oco('USDT_IRT', 'buy', 51000, 100, 50500, 52000)
);

// List orders (pagination)
$orders = Bitpin::getOrders(0, 20);
// Returns: Order[] with state, amounts, commissions, fill ratio

// Check order status
if ($order->isOpen()) { ... }
if ($order->isClosed()) { ... }
$fillRatio = $order->getFillRatio(); // 0.0 to 1.0
```

### WebSocket

```php
$wsUrl = Bitpin::getWebSocketUrl();
// wss://centrifugo.bitpin.org/connection/websocket

// Use with centrifuge-php or any Centrifuge client
// Public channels: matches:SYMBOL, orderbook:SYMBOL, market:SYMBOL
// Private channels: user:order_info#USER_IDENTIFIER (requires JWT)
```

## DTO Helpers

All DTOs provide helper methods:

```php
// Ticker
$ticker->getPriceAsFloat();
$ticker->getLowAsFloat();
$ticker->getHighAsFloat();

// OrderBook
$orderBook->getAsksParsed(); // [{price: float, amount: float}, ...]
$orderBook->getBidsParsed();
$orderBook->getBestAsk();
$orderBook->getBestBid();
$orderBook->getSpread();

// Match
$match->getPriceAsFloat();
$match->getBaseAmountAsFloat();
$match->isBuy();
$match->isSell();

// Candle
$candle->getDateTime(); // DateTime object
$candle->isBullish();
$candle->isBearish();
$candle->getVolumeAsFloat();

// Order
$order->getPriceAsFloat();
$order->getFillRatio();
$order->isOpen();
$order->isClosed();
```

## Error Handling

```php
use Bitpin\Api\Exceptions\BitpinApiException;

try {
    $order = Bitpin::createOrder($request);
} catch (BitpinApiException $e) {
    echo "HTTP Status: {$e->getHttpStatusCode()}";
    echo "Message: {$e->getMessage()}";
    echo "Details: " . json_encode($e->getErrorDetails());

    // Specific exception types
    match ($e->getHttpStatusCode()) {
        401 => $this->handleAuthError(),
        429 => $this->handleRateLimit($e->getErrorDetails()['retry_after'] ?? 60),
        403 => $this->handleIpNotAllowed(),
        404 => $this->handleNotFound(),
        500, 503 => $this->handleServerError(),
        default => $this->handleGenericError($e),
    };
}
```

### Exception Types

| Exception | HTTP Code | When |
|-----------|-----------|------|
| `authenticationRequired()` | 401 | No token set for authenticated endpoint |
| `tokenExpired()` | 401 | Access token expired |
| `invalidCredentials()` | 401 | Wrong API key/secret |
| `ipNotAllowed()` | 403 | IP not in allowed list |
| `rateLimited()` | 429 | Rate limit exceeded |
| `notFound()` | 404 | Resource not found |
| `badRequest()` | 400 | Invalid parameters |
| `serverError()` | 500 | Server error |
| `serviceUnavailable()` | 503 | Service down |

## Configuration Options

```php
// config/bitpin.php
return [
    'api_key' => env('BITPIN_API_KEY'),
    'secret_key' => env('BITPIN_SECRET_KEY'),
    'base_url' => env('BITPIN_BASE_URL', 'https://api.bitpin.org'),
    'websocket_url' => env('BITPIN_WEBSOCKET_URL', 'wss://centrifugo.bitpin.org/connection/websocket'),
    'timeout' => env('BITPIN_TIMEOUT', 30),
    'connect_timeout' => env('BITPIN_CONNECT_TIMEOUT', 10),
    'retries' => env('BITPIN_RETRIES', 3),
    'retry_delay' => env('BITPIN_RETRY_DELAY', 1000),
    'auto_refresh_token' => env('BITPIN_AUTO_REFRESH_TOKEN', true),
    'token_refresh_buffer' => env('BITPIN_TOKEN_REFRESH_BUFFER', 60),
    'logging' => [
        'enabled' => env('BITPIN_LOGGING_ENABLED', false),
        'channel' => env('BITPIN_LOG_CHANNEL', 'default'),
        'level' => env('BITPIN_LOG_LEVEL', 'debug'),
    ],
];
```

## Rate Limits (Reference)

| Endpoint | Limit |
|----------|-------|
| Public API default | 300/min/IP |
| Authenticate/Refresh | 200/day |
| Currencies/Markets/Networks (anon) | 10,000/day |
| Currencies/Markets/Networks (auth) | 200/min |
| Tickers | 80/min |
| Orderbook/Matches | 60/min |
| Candles | 300/min/IP |
| Commissions | 1000/day |
| Wallet Networks v2 | 60/min |

## Testing

```bash
# Run tests
./vendor/bin/phpunit

# With coverage
./vendor/bin/phpunit --coverage-html coverage
```

## License

MIT License - see LICENSE file for details.

## Support

- [Bitpin API Documentation](https://docs.bitpin.ir/v1)
- [Bitpin Website](https://bitpin.org)
- Issues: GitHub Issues

---

**Note**: This SDK is based on the Bitpin API documentation as of October 2026. Some endpoints (Wallet, full Orders) are partially documented in the source. The SDK will be updated as the API evolves.