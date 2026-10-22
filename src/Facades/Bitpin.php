<?php

namespace Bitpin\Api\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Bitpin\Api\DTOs\AuthTokens authenticate(string $apiKey, string $secretKey)
 * @method static \Bitpin\Api\DTOs\AccessToken refreshAccessToken(string $refreshToken)
 * @method static void setAccessToken(string $accessToken)
 * @method static string|null getAccessToken()
 * @method static bool isAuthenticated()
 * @method static \Bitpin\Api\DTOs\Currency[] getCurrencies()
 * @method static \Bitpin\Api\DTOs\MarketData[] getMarkets()
 * @method static \Bitpin\Api\DTOs\Network[] getNetworks(?string $code = null, ?bool $canDeposit = null, ?bool $canWithdraw = null)
 * @method static \Bitpin\Api\DTOs\NetworkWithMaintenance[] getNetworksForCurrency(string $currencyCode)
 * @method static \Bitpin\Api\DTOs\Ticker[] getTickers()
 * @method static \Bitpin\Api\DTOs\OrderBook getOrderBook(string $symbol)
 * @method static \Bitpin\Api\DTOs\TradeMatch[] getMatches(string $symbol)
 * @method static \Bitpin\Api\DTOs\Candle[] getCandles(string $symbol, int $from, int $to, string $resolution = '30m')
 * @method static \Bitpin\Api\DTOs\Commission[] getCommissions()
 * @method static \Bitpin\Api\DTOs\Order createOrder(\Bitpin\Api\DTOs\OrderRequest $request)
 * @method static \Bitpin\Api\DTOs\Order[] getOrders(int $offset = 0, int $limit = 10)
 * @method static string getWebSocketUrl()
 *
 * @see \Bitpin\Api\Services\BitpinClient
 */
class Bitpin extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bitpin';
    }
}