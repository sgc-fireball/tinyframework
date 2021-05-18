<?php declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Localization\TranslationLoader;
use TinyFramework\Localization\Translator;
use TinyFramework\Localization\TranslatorInterface;

class LocalizationServiceProvider extends ServiceProviderAwesome
{

    public function register()
    {
        $this->container
            ->alias('translation.loader', TranslationLoader::class)
            ->singleton(TranslationLoader::class, function () {
                return new TranslationLoader();
            });
        $this->container
            ->alias('translator', Translator::class)
            ->alias(TranslatorInterface::class, Translator::class)
            ->singleton(Translator::class, function () {
                $config = $this->container->get('config')->get('app');
                return new Translator($this->container->get(TranslationLoader::class), $config['locale']);
            });
    }

}
