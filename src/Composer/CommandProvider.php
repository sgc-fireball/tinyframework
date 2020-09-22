<?php declare(strict_types=1);

namespace TinyFramework\Composer;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use TinyFramework\Console\ConsoleKernel;
use TinyFramework\Core\Container;

class CommandProvider implements CommandProviderCapability
{

    public function getCommands()
    {
        /** @var ConsoleKernel $console */
        $console = Container::instance()->get(ConsoleKernel::class);
        return $console->getCommands();
    }

}
