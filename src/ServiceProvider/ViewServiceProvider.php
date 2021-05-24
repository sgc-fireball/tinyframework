<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ContainerInterface;
use TinyFramework\Template\ViewInterface;

class ViewServiceProvider extends ServiceProviderAwesome
{

    public function register(): void
    {
        $configs = $this->container->get('config')->get('view');
        if (is_null($configs)) {
            return;
        }
        $this->container->alias('view', $configs['default'])->alias(ViewInterface::class, $configs['default']);
        unset($configs['default']);

        foreach ($configs as $name => $config) {
            $class = $config['driver'];
            unset($config['driver']);
            $this->container
                ->alias($name, $class)
                ->singleton($class, function () use ($class, $config) {
                    return $this->container->call($class, ['config' => $config]);
                });
        }
    }

}
