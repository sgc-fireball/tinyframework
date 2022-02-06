<?php

declare(strict_types=1);

namespace TinyFramework\Console;

use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Core\KernelInterface;

interface ConsoleKernelInterface extends KernelInterface
{
    public function handle(InputInterface $input, OutputInterface $output): int;

    public function getCommands(): array;
}
