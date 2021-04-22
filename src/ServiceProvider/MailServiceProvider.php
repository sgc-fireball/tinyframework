<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ContainerInterface;
use TinyFramework\Mail\Mailer;
use Swift_Transport;
use Swift_Mailer;

class MailServiceProvider extends ServiceProviderAwesome
{

    public function register()
    {
        $this->container
            ->alias('mailer', Mailer::Class)
            ->singleton(Mailer::class, function () {
                if (!class_exists(Swift_Mailer::class)) {
                    throw new \RuntimeException('MailServiceProvider: Could not found class Swift_Mailer.');
                }

                $config = $this->container->get('config')->get('mail');
                $driver = $config['default'];
                $fromAddress = ($config['from'] ?? [])['address'] ?? null;
                $fromName = ($config['from'] ?? [])['name'] ?? null;
                $config = $config[$driver] ?? [];
                $driver = $config['driver'];
                unset($config['driver']);

                $mailer = new Mailer();
                $mailer->from($fromAddress, $fromName ?? $fromAddress);
                $mailer->mailer(
                    new Swift_Mailer(
                        $this->setParameterFromArray(
                            $adapter = $this->container->call($driver, $config),
                            $config
                        )
                    )
                );
                return $mailer;
            });
    }

    private function setParameterFromArray(Swift_Transport $adapter, array $config): Swift_Transport
    {
        foreach ($config as $key => $value) {
            $key = preg_replace_callback("/_[a-z]/", function ($match) {
                return ltrim(strtoupper($match[0]), '_');
            }, $key);
            $method = 'set' . ucfirst($key);
            if (method_exists($adapter, '__call') || method_exists($adapter, $method) && !is_null($value)) {
                try {
                    $adapter->{$method}($value);
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }
        return $adapter;
    }

}
