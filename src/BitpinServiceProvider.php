<?php

namespace Bitpin\Api;

use Illuminate\Support\ServiceProvider;
use Bitpin\Api\Contracts\BitpinClientInterface;
use Bitpin\Api\Services\BitpinClient;

class BitpinServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/bitpin.php',
            'bitpin'
        );

        $this->app->singleton(BitpinClientInterface::class, function ($app) {
            return new BitpinClient($app['config']['bitpin']);
        });

        $this->app->alias(BitpinClientInterface::class, 'bitpin');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/bitpin.php' => config_path('bitpin.php'),
            ], 'bitpin-config');
        }
    }

    public function provides(): array
    {
        return [BitpinClientInterface::class, 'bitpin'];
    }
}