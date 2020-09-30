<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ContainerInterface;
use TinyFramework\Hash\HashInterface;

class HashServiceProvider extends ServiceProviderAwesome
{

    public function register()
    {
        $configs = $this->container->get('config')->get('hash');
        if (is_null($configs)) {
            return;
        }
        $class = $configs[$configs['default']]['driver'];
        $this->container->alias('hash', $class)->alias(HashInterface::class, $class);
        unset($configs['default']);

        foreach ($configs as $name => $config) {
            $class = $config['driver'];
            unset($config['driver']);
            $this->container
                ->alias($name, $class)
                ->singleton($class, function (ContainerInterface $container) use ($class, $config) {
                    return $this->container->call($class, $config);
                });
        }
    }

}
