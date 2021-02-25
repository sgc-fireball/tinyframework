<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ContainerInterface;
use TinyFramework\Localization\TranslationLoader;
use TinyFramework\Localization\Translator;

class LocalizationServiceProvider extends ServiceProviderAwesome
{

    public function register()
    {
        $this->container
            ->alias('translation.loader', TranslationLoader::class)
            ->singleton(TranslationLoader::class, function (ContainerInterface $container) {
                return new TranslationLoader();
            });
        $this->container
            ->alias('translator', Translator::class)
            ->singleton(Translator::class, function (ContainerInterface $container) {
                $config = $container->get('config')->get('app');
                return new Translator($container->get(TranslationLoader::class), $config['locale']);
            });
    }

}
