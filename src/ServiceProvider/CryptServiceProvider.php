<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ContainerInterface;
use TinyFramework\Crypt\CryptInterface;

class CryptServiceProvider extends ServiceProviderAwesome
{

    public function register()
    {
        $configs = $this->container->get('config')->get('crypt');
        if (is_null($configs)) {
            return;
        }
        $class = $configs[$configs['default']]['driver'];
        $this->container->alias('crypt', $class)->alias(CryptInterface::class, $class);
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
