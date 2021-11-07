<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\Config;
use TinyFramework\Core\ConfigInterface;

class ConfigServiceProvider extends ServiceProviderAwesome
{

    public function register(): void
    {
        $this->container
            ->alias('config', Config::Class)
            ->alias(ConfigInterface::class, Config::Class)
            ->singleton(Config::class, function () {
                return new Config(['base_path' => getcwd()]);
            });
    }

}
