# SDK زبان PHP برای API صرافی بیت‌پین

یک SDK جامع برای کار با [مستندات API صرافی بیت‌پین](https://docs.bitpin.ir/v1) که با در نظر گرفتن یکپارچه‌سازی با Laravel طراحی شده است. این پکیج برای ارسال درخواست‌های HTTP از کتابخانه Guzzle استفاده می‌کند.

🌐 **زبان‌های دیگر:** [English](README_EN.md) | فارسی


## ✨ امکانات

- ✅ **پوشش کامل REST API:** پیاده‌سازی endpointهای مستندشده API نسخه‌های v1 و v2
- ✅ **یکپارچه‌سازی با Laravel:** ارائه Service Provider، Facade و امکان انتشار فایل تنظیمات
- ✅ **مدیریت خودکار توکن:** تمدید خودکار Access Token منقضی‌شده
- ✅ **DTOهای تایپ‌شده:** تبدیل پاسخ‌های API به کلاس‌های Data Transfer Object با ساختار مشخص
- ✅ **مدیریت خطاها:** سلسله‌مراتب اختصاصی Exceptionها به همراه کد وضعیت HTTP
- ✅ **مدیریت محدودیت درخواست‌ها:** تلاش مجدد خودکار با تأخیر افزایشی (Exponential Backoff)
- ✅ **پشتیبانی از ثبت گزارش:** ثبت اختیاری درخواست‌ها و پاسخ‌ها برای عیب‌یابی
- ✅ **پشتیبانی از WebSocket:** ابزارهای کمکی برای اتصال به Centrifugo
- ✅ **سازگار با PHP 8.1 و بالاتر:** استفاده از قابلیت‌های مدرن PHP

## 📦 نصب

برای نصب پکیج با Composer دستور زیر را اجرا کنید:

```bash
composer require zedxpm/bitpin-api-php
```

### راه‌اندازی در Laravel

Service Provider به‌صورت خودکار شناسایی می‌شود. برای انتشار فایل تنظیمات، دستور زیر را اجرا کنید:

```bash
php artisan vendor:publish --tag=bitpin-config
```

سپس اطلاعات موردنیاز را در فایل `.env` قرار دهید:

```dotenv
BITPIN_API_KEY=your_api_key_here
BITPIN_SECRET_KEY=your_secret_key_here
BITPIN_BASE_URL=https://api.bitpin.org
BITPIN_WEBSOCKET_URL=wss://centrifugo.bitpin.org/connection/websocket
BITPIN_TIMEOUT=30
BITPIN_AUTO_REFRESH_TOKEN=true
BITPIN_LOGGING_ENABLED=false
```

## 🚀 شروع سریع

### استفاده از Facade در Laravel

```php
use Bitpin\Api\Facades\Bitpin;
use Bitpin\Api\DTOs\OrderRequest;

// احراز هویت و دریافت توکن‌ها
$tokens = Bitpin::authenticate(
    config('bitpin.api_key'),
    config('bitpin.secret_key')
);

// دریافت اطلاعات بازار
$tickers = Bitpin::getTickers();
$markets = Bitpin::getMarkets();
$orderBook = Bitpin::getOrderBook('BTC_IRT');

// ثبت سفارش محدود
$order = Bitpin::createOrder(
    OrderRequest::limit('USDT_IRT', 'buy', 50000, 100)
);
```

### استفاده از Dependency Injection

```php
use Bitpin\Api\Contracts\BitpinClientInterface;
use Bitpin\Api\DTOs\OrderRequest;

class TradingService
{
    public function __construct(
        protected BitpinClientInterface $bitpin
    ) {}

    public function placeLimitOrder(
        string $symbol,
        string $side,
        float $price,
        float $amount
    ) {
        return $this->bitpin->createOrder(
            OrderRequest::limit(
                $symbol,
                $side,
                $price,
                $amount
            )
        );
    }
}
```

### استفاده مستقل از Laravel

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

$tokens = $client->authenticate(
    $config['api_key'],
    $config['secret_key']
);

$currencies = $client->getCurrencies();
```

## 📚 مرجع API

### احراز هویت (Authentication)

| متد | توضیحات |
|---|---|
| `authenticate($apiKey, $secretKey)` | دریافت Access Token و Refresh Token |
| `refreshAccessToken($refreshToken)` | دریافت توکن دسترسی جدید با استفاده از Refresh Token |
| `setAccessToken($token)` | تنظیم دستی Access Token |
| `getAccessToken()` | دریافت توکن دسترسی فعلی |
| `isAuthenticated()` | بررسی وضعیت احراز هویت |

### اطلاعات بازار (Market Data)

#### ارزها و بازارها

```php
// دریافت فهرست ارزها
$currencies = Bitpin::getCurrencies();

// دریافت فهرست بازارها
$markets = Bitpin::getMarkets();

// دریافت شبکه‌های انتقال در API نسخه v1
$networks = Bitpin::getNetworks();

// فیلتر شبکه‌ها بر اساس پارامترهای متد
$networks = Bitpin::getNetworks('TRX', true, false);

// دریافت شبکه‌های یک ارز در API نسخه v2
// این متد به احراز هویت نیاز دارد
$networks = Bitpin::getNetworksForCurrency('USDT');
```

#### قیمت‌ها، دفتر سفارش و معاملات

```php
// دریافت قیمت‌های لحظه‌ای بازار
$tickers = Bitpin::getTickers();

// دریافت دفتر سفارش
$orderBook = Bitpin::getOrderBook('BTC_IRT');

$bestAsk = $orderBook->getBestAsk();
$bestBid = $orderBook->getBestBid();
$spread = $orderBook->getSpread();

// دریافت معاملات اخیر
$matches = Bitpin::getMatches('BTC_IRT');

// دریافت کندل‌های قیمتی (OHLCV)
$candles = Bitpin::getCandles(
    'BTC_IRT',
    1735689600,
    1735776000,
    '1h'
);

// دریافت کارمزد معاملات؛ نیازمند احراز هویت
$commissions = Bitpin::getCommissions();
```

بازه‌های زمانی پشتیبانی‌شده برای کندل‌ها:

`1m`, `5m`, `15m`, `30m`, `1h`, `3h`, `4h`, `6h`, `1d`, `1w`, `1M`

ساختارهای بازگشتی بر اساس پیاده‌سازی SDK شامل DTOهایی مانند `Currency`، `Market`، `Ticker`، `OrderBook`، `Match`، `Candle` و `Commission` هستند.

### مدیریت سفارش‌ها (Orders)

SDK از ساخت انواع مختلف سفارش پشتیبانی می‌کند:

```php
use Bitpin\Api\DTOs\OrderRequest;

// سفارش محدود (Limit)
$order = Bitpin::createOrder(
    OrderRequest::limit(
        'USDT_IRT',
        'buy',
        50000,
        100
    )
);

// سفارش بازار (Market)
$order = Bitpin::createOrder(
    OrderRequest::market(
        'USDT_IRT',
        'sell',
        50
    )
);

// سفارش توقف-محدود (Stop-Limit)
$order = Bitpin::createOrder(
    OrderRequest::stopLimit(
        'USDT_IRT',
        'buy',
        51000,
        100,
        50500
    )
);

// سفارش OCO
$order = Bitpin::createOrder(
    OrderRequest::oco(
        'USDT_IRT',
        'buy',
        51000,
        100,
        50500,
        52000
    )
);

// دریافت سفارش‌ها با صفحه‌بندی
$orders = Bitpin::getOrders(0, 20);

// بررسی وضعیت سفارش
if ($order->isOpen()) {
    // سفارش باز است
}

if ($order->isClosed()) {
    // سفارش بسته شده است
}

// نسبت پرشدن سفارش، عددی بین 0 و 1
$fillRatio = $order->getFillRatio();
```

**توجه:** پیش از استفاده از سفارش‌های واقعی، پارامترها، محدودیت‌های بازار و ساختار دقیق endpointها را با مستندات رسمی بیت‌پین بررسی کنید.

### اتصال WebSocket

برای دریافت نشانی WebSocket از متد زیر استفاده کنید:

```php
$wsUrl = Bitpin::getWebSocketUrl();
```

نشانی پیش‌فرض:

```text
wss://centrifugo.bitpin.org/connection/websocket
```

کانال‌های معرفی‌شده:

- عمومی: `matches:SYMBOL`
- عمومی: `orderbook:SYMBOL`
- عمومی: `market:SYMBOL`
- خصوصی: `user:order_info#USER_IDENTIFIER` (نیازمند JWT)

برای برقراری اتصال می‌توانید از کتابخانه‌های سازگار با Centrifugo استفاده کنید.

## 🧩 متدهای کمکی DTOها

DTOها متدهای کمکی برای کار ساده‌تر با داده‌های دریافتی ارائه می‌کنند.

### Ticker

```php
$ticker->getPriceAsFloat();
$ticker->getLowAsFloat();
$ticker->getHighAsFloat();
```

### OrderBook

```php
$orderBook->getAsksParsed();
$orderBook->getBidsParsed();
$orderBook->getBestAsk();
$orderBook->getBestBid();
$orderBook->getSpread();
```

متدهای `getAsksParsed()` و `getBidsParsed()` داده‌ها را به آرایه‌هایی با مقادیر عددی تبدیل می‌کنند.

### Match

```php
$match->getPriceAsFloat();
$match->getBaseAmountAsFloat();
$match->isBuy();
$match->isSell();
```

### Candle

```php
$candle->getDateTime();
$candle->isBullish();
$candle->isBearish();
$candle->getVolumeAsFloat();
```

متد `getDateTime()` یک شیء از نوع `DateTime` برمی‌گرداند.

### Order

```php
$order->getPriceAsFloat();
$order->getFillRatio();
$order->isOpen();
$order->isClosed();
```

## ⚠️ مدیریت خطاها

برای مدیریت خطاهای API از `BitpinApiException` استفاده کنید:

```php
use Bitpin\Api\Exceptions\BitpinApiException;

try {
    $order = Bitpin::createOrder($request);
} catch (BitpinApiException $e) {
    echo "HTTP Status: {$e->getHttpStatusCode()}";

    echo "Message: {$e->getMessage()}";

    echo 'Details: ' . json_encode(
        $e->getErrorDetails()
    );

    match ($e->getHttpStatusCode()) {
        401 => $this->handleAuthError(),

        429 => $this->handleRateLimit(
            $e->getErrorDetails()['retry_after'] ?? 60
        ),

        403 => $this->handleIpNotAllowed(),

        404 => $this->handleNotFound(),

        500, 503 => $this->handleServerError(),

        default => $this->handleGenericError($e),
    };
}
```

### انواع خطاها

| نوع خطا یا وضعیت | کد HTTP | توضیحات |
|---|---:|---|
| `authenticationRequired()` | 401 | توکن برای endpoint نیازمند احراز هویت تنظیم نشده است |
| `tokenExpired()` | 401 | توکن دسترسی منقضی شده است |
| `invalidCredentials()` | 401 | کلید API یا کلید محرمانه نامعتبر است |
| `ipNotAllowed()` | 403 | آدرس IP مجاز نیست |
| `rateLimited()` | 429 | محدودیت تعداد درخواست‌ها رد شده است |
| `notFound()` | 404 | منبع موردنظر پیدا نشد |
| `badRequest()` | 400 | پارامترهای درخواست نامعتبر هستند |
| `serverError()` | 500 | خطای داخلی سرور |
| `serviceUnavailable()` | 503 | سرویس در دسترس نیست |

## ⚙️ تنظیمات

نمونه فایل `config/bitpin.php`:

```php
return [
    'api_key' => env('BITPIN_API_KEY'),
    'secret_key' => env('BITPIN_SECRET_KEY'),

    'base_url' => env(
        'BITPIN_BASE_URL',
        'https://api.bitpin.org'
    ),

    'websocket_url' => env(
        'BITPIN_WEBSOCKET_URL',
        'wss://centrifugo.bitpin.org/connection/websocket'
    ),

    'timeout' => env('BITPIN_TIMEOUT', 30),
    'connect_timeout' => env('BITPIN_CONNECT_TIMEOUT', 10),

    'retries' => env('BITPIN_RETRIES', 3),
    'retry_delay' => env('BITPIN_RETRY_DELAY', 1000),

    'auto_refresh_token' => env(
        'BITPIN_AUTO_REFRESH_TOKEN',
        true
    ),

    'token_refresh_buffer' => env(
        'BITPIN_TOKEN_REFRESH_BUFFER',
        60
    ),

    'logging' => [
        'enabled' => env('BITPIN_LOGGING_ENABLED', false),
        'channel' => env('BITPIN_LOG_CHANNEL', 'default'),
        'level' => env('BITPIN_LOG_LEVEL', 'debug'),
    ],
];
```

| گزینه | توضیحات |
|---|---|
| `api_key` | کلید API |
| `secret_key` | کلید محرمانه API |
| `base_url` | نشانی پایه API |
| `websocket_url` | نشانی اتصال WebSocket |
| `timeout` | حداکثر زمان اجرای درخواست |
| `connect_timeout` | حداکثر زمان برقراری اتصال |
| `retries` | تعداد تلاش‌های مجدد |
| `retry_delay` | تأخیر پایه بین تلاش‌ها، بر حسب میلی‌ثانیه |
| `auto_refresh_token` | فعال‌سازی تمدید خودکار توکن |
| `token_refresh_buffer` | فاصله زمانی پیش از انقضا برای تمدید توکن، بر حسب ثانیه |
| `logging.enabled` | فعال‌سازی ثبت گزارش‌ها |
| `logging.channel` | کانال ثبت گزارش |
| `logging.level` | سطح ثبت گزارش |

## 🚦 محدودیت نرخ درخواست‌ها

جدول زیر مقادیر مرجع درج‌شده در مستندات این SDK را نشان می‌دهد. محدودیت‌های واقعی ممکن است با توجه به سیاست‌های فعلی سرویس تغییر کنند.

| endpoint یا گروه | محدودیت مرجع |
|---|---|
| API عمومی | ۳۰۰ درخواست در دقیقه به‌ازای هر IP |
| احراز هویت و تمدید توکن | ۲۰۰ درخواست در روز |
| ارزها، بازارها و شبکه‌ها (بدون احراز هویت) | ۱۰٬۰۰۰ درخواست در روز |
| ارزها، بازارها و شبکه‌ها (با احراز هویت) | ۲۰۰ درخواست در دقیقه |
| تیکرها | ۸۰ درخواست در دقیقه |
| دفتر سفارش و معاملات اخیر | ۶۰ درخواست در دقیقه |
| کندل‌ها | ۳۰۰ درخواست در دقیقه به‌ازای هر IP |
| کارمزدها | ۱٬۰۰۰ درخواست در روز |
| شبکه‌های کیف پول در v2 | ۶۰ درخواست در دقیقه |

این اعداد تضمین‌کننده محدودیت فعلی حساب شما نیستند؛ برای استفاده عملی، مستندات رسمی و پاسخ‌های سرور را بررسی کنید.

## 📄 مجوز

این پروژه تحت مجوز MIT منتشر می‌شود. برای اطلاعات بیشتر، فایل `LICENSE` را مطالعه کنید.

## 🔗 منابع و پشتیبانی

- [مستندات رسمی API بیت‌پین](https://docs.bitpin.ir/v1)
- [وب‌سایت بیت‌پین](https://bitpin.org)
- گزارش مشکلات و پیشنهادها: بخش Issues مخزن GitHub پروژه

---

**یادداشت:** این SDK بر اساس مستندات API بیت‌پین تا اکتبر ۲۰۲۶ تهیه شده است. برخی endpointها، از جمله بخش‌هایی از کیف پول و سفارش‌ها، ممکن است هنوز به‌صورت کامل مستند یا پیاده‌سازی نشده باشند. برای اطلاع از وضعیت دقیق هر قابلیت، کد منبع و مستندات رسمی را بررسی کنید.
