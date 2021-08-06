<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Database\DatabaseInterface;
use TinyFramework\Database\MigrationInstaller;

class DatabaseServiceProvider extends ServiceProviderAwesome
{

    public function register(): void
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
        $this->container
            ->singleton(MigrationInstaller::class, function () {
                return new MigrationInstaller(
                    $this->container->get(OutputInterface::class),
                    $this->container->get(DatabaseInterface::class),
                    $this->container->tagged('migration')
                );
            });
    }

}
