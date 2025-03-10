<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\Config;
use TinyFramework\Core\ConfigInterface;
use TinyFramework\Helpers\DateTime;

class ConfigServiceProvider extends ServiceProviderAwesome
{
    public function register(): void
    {
        $this->container
            ->alias('config', Config::class)
            ->alias(ConfigInterface::class, Config::class)
            ->singleton(Config::class, function () {
                return new Config(['base_path' => getcwd()]);
            });
    }

    public function boot(): void
    {
        DateTime::setDefaultTimeZone(config('app.timezone'));
    }
}
