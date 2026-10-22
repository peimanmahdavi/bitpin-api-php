<?php

namespace Bitpin\Api\Contracts;

use Bitpin\Api\DTOs\{
    AuthTokens, AccessToken, Currency, MarketData, Network, NetworkWithMaintenance,
    Ticker, OrderBook, TradeMatch, Candle, Commission, Order, OrderRequest
};

interface BitpinClientInterface
{
    // Authentication
    public function authenticate(string $apiKey, string $secretKey): AuthTokens;
    public function refreshAccessToken(string $refreshToken): AccessToken;
    public function setAccessToken(string $accessToken): void;
    public function getAccessToken(): ?string;
    public function isAuthenticated(): bool;

    // Market Data - Currencies
    public function getCurrencies(): array;

    // Market Data - Markets
    public function getMarkets(): array;

    // Market Data - Networks
    public function getNetworks(?string $code = null, ?bool $canDeposit = null, ?bool $canWithdraw = null): array;
    public function getNetworksForCurrency(string $currencyCode): array;

    // Market Data - Tickers
    public function getTickers(): array;

    // Market Data - Order Book
    public function getOrderBook(string $symbol): OrderBook;

    // Market Data - Matches (Recent Trades)
    public function getMatches(string $symbol): array;

    // Market Data - Candles (OHLCV)
    public function getCandles(string $symbol, int $from, int $to, string $resolution = '30m'): array;

    // Market Data - Commissions
    public function getCommissions(): array;

    // Orders
    public function createOrder(OrderRequest $request): Order;
    public function getOrders(int $offset = 0, int $limit = 10): array;

    // WebSocket
    public function getWebSocketUrl(): string;
}