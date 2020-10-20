<?php declare(strict_types=1);

namespace TinyFramework\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\Capability\CommandProvider as BaseCommandProvider;

/**
 * @see https://getcomposer.org/doc/articles/plugins.md#creating-a-plugin
 * @see https://github.com/composer/composer/blob/2b86df40039e82f148bd50b47be4aa848a6ad107/doc/articles/plugins.md
 */
class Plugin implements PluginInterface, Capable
{

    protected Composer $composer;

    protected IOInterface $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        // composer-plugin-api@1 & composer-plugin-api@2
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // composer-plugin-api@2
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // composer-plugin-api@2
    }

    public function getCapabilities()
    {
        return [BaseCommandProvider::class => CommandProvider::class];
    }

}
