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
        if ($config === null) {
            return;
        }
        foreach ($config as $connection => $settings) {
            $this->container
                ->singleton('database.' . $connection, function () use ($settings) {
                    $class = $settings['driver'];
                    return new $class($settings);
                });
        }
        $this->container
            ->alias('database', 'database.' . $config['default'])
            ->alias(DatabaseInterface::class, 'database.' . $config['default']);
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
