<?php declare(strict_types=1);

namespace TinyFramework\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface CommandInterface
{

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output);

}
