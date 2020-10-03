<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\Config;
use TinyFramework\Core\ConfigInterface;
use TinyFramework\Core\ContainerInterface;
use TinyFramework\Mail\Mailer;
use Swift_Transport;
use Swift_Mailer;

class MailServiceProvider extends ServiceProviderAwesome
{

    public function register()
    {
        if (!class_exists(Swift_Mailer::class)) {
            $this->container->get('logger')->warning('MailServiceProvider: Could not found class Swift_Mailer.');
            return null;
        }
        $this->container
            ->alias('mailer', Mailer::Class)
            ->singleton(Mailer::class, function (ContainerInterface $container) {
                $config = $this->container->get('config')->get('mail');
                $driver = $config['default'];
                $fromEmail = ($config['from'] ?? [])['email'] ?? null;
                $fromName = ($config['name'] ?? [])['name'] ?? null;
                $config = $config[$driver] ?? [];
                $driver = $config['driver'];
                unset($config['driver']);

                $adapter = $container->call($driver, $config);

                $mailer = new Mailer();
                $mailer->from($fromEmail, $fromName ?? $fromEmail);
                $mailer->mailer($adapter);
                return $mailer;
            });
    }

    private function setParameterFromArray(Swift_Transport $adapter, array $config)
    {
        foreach ($config as $key => $value) {
            $method = 'set' . ucfirst($value);
            if (method_exists($adapter, $method)) {
                $adapter->{$method}($value);
            }
        }
    }

}
