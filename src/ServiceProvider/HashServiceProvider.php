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

        foreach ($configs as $name => $config) {
            if ($name === 'default') {
                continue;
            }
            $class = $config['driver'];
            $parameters = $config;
            unset($parameters['driver']);
            $this->container
                ->singleton($name, function (ContainerInterface $container) use ($class, $parameters) {
                    return $this->container->call($class, $parameters);
                });
        }

        $this->container
            ->alias('hash', $configs['default'])
            ->alias(HashInterface::class, $configs['default']);
    }

}
