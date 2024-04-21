<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\FileSystem\FileSystemInterface;
use TinyFramework\Http\Controller\DownloadController;
use TinyFramework\Http\Router;

class FileSystemServiceProvider extends ServiceProviderAwesome
{
    public function register(): void
    {
        $config = $this->container->get('config')->get('filesystem');
        if ($config === null) {
            return;
        }
        foreach ($config as $provider => $settings) {
            $this->container
                ->singleton('filesystem.' . $provider, function () use ($provider, $settings) {
                    $class = $settings['driver'];
                    $settings['name'] = $provider;
                    return $this->container->call($class, ['config' => $settings]);
                });
        }
        $this->container
            ->alias('filesystem', 'filesystem.' . $config['default'])
            ->alias(FileSystemInterface::class, 'filesystem.' . $config['default']);
    }

    public function boot(): void
    {
        /** @var Router $router */
        $router = $this->container->get('router');
        $router
            ->pattern('fsDisk', '[a-zA-Z0-9_]+')
            ->pattern('fsPath', '.*')
            ->get('__download/{fsDisk}/{fsPath}', [DownloadController::class, 'download'])
            ->name('filesystem.download');
    }
}
