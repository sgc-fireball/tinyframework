<?php declare(strict_types=1);

namespace TinyFramework\Console;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use TinyFramework\Core\KernelInterface;

interface ConsoleKernelInterface extends KernelInterface
{

    /**
     * @return BaseCommand[]
     */
    public function getCommands(): array;

    public function handle(ArgvInput $input, ConsoleOutput $output): int;

}
