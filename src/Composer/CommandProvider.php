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
        return []; // @TODO or remove
    }

}
