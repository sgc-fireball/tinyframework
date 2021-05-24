<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Mail\Mailer;
use TinyFramework\Mail\MailerInterface;

class MailServiceProvider extends ServiceProviderAwesome
{

    public function register(): void
    {
        $globalConfig = $this->container->get('config')->get('mail');
        $config = $globalConfig[$globalConfig['default']] ?? [];
        $config['from_address'] = $globalConfig['from']['address'] ?? null;
        $config['from_name'] = $globalConfig['from']['name'] ?? null;

        $this->container
            ->alias('mail', $config['driver'])
            ->alias('mailer', $config['driver'])
            ->alias(MailerInterface::class, $config['driver'])
            ->singleton($config['driver'], function () use ($config) {
                $class = $config['driver'];
                return new $class($config);
            });
    }

}
