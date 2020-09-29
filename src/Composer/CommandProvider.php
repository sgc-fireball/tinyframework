<?php declare(strict_types=1);

namespace TinyFramework\Composer;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use TinyFramework\Console\ConsoleKernel;
use TinyFramework\Core\Container;
use TinyFramework\Core\DotEnv;
use TinyFramework\Core\DotEnvInterface;

class CommandProvider implements CommandProviderCapability
{

    public function getCommands()
    {
        /** @var ConsoleKernel $console */
        $console = Container::instance()
            ->singleton(DotEnvInterface::class, DotEnv::class)
            ->get(ConsoleKernel::class);
        return $console->getCommands();
    }

}
