<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Template\Blade;
use TinyFramework\Template\ViewInterface;

class ViewServiceProvider extends ServiceProviderAwesome
{
    public function register(): void
    {
        $configs = $this->container->get('config')->get('view');
        if ($configs === null) {
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
                    $engine = $this->container->call($class, ['config' => $config]);
                    if ($engine instanceof Blade) {
                        $this->registerBladeDirective($engine);
                    }
                    return $engine;
                });
        }
    }

    private function registerBladeDirective(Blade $engine): void
    {
        $engine->addDirective('csrf', function (string $expression): string {
            $token = $this->container->get('request')?->session()?->get('csrf-token') ?? '';
            return sprintf('<input type="hidden" name="_token" value="%s">', $token);
        });
        $engine->addDirective('inject', function (string $expression): string {
            [$variable, $service] = explode(',', (string)preg_replace("/[\(\)\\\"\']/", '', $expression));
            return sprintf('<?php $%s = container("%s"); ?>', trim($variable), trim($service));
        });
        $engine->addDirective('dump', function (string $expression): string {
            return sprintf('<?php dump%s ?>', $expression);
        });
        $engine->addDirective('dd', function (string $expression): string {
            return sprintf('<?php dd%s; ?>', $expression);
        });
        $engine->addDirective('trans', function (string $expression): string {
            return sprintf('<?php _%s; ?>', $expression);
        });
    }
}
