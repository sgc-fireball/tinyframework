<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Database\DatabaseInterface;

class DatabaseServiceProvider extends ServiceProviderAwesome
{

    public function register()
    {
        $config = $this->container->get('config')->get('database');
        if (is_null($config)) {
            return;
        }
        $config = $config[$config['default']] ?? [];
        $this->container
            ->alias('database', $config['driver'])
            ->alias(DatabaseInterface::class, $config['driver'])
            ->singleton($config['driver'], function () use ($config) {
                $class = $config['driver'];
                return new $class($config);
            });
    }

}
