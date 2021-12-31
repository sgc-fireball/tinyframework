<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;

class TinyframeworkUpCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Bring the application out of maintenance mode.');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        if (file_exists('storage/maintenance.json')) {
            @unlink('storage/maintenance.json');
        }
        $this->output->writeln('<green>Application is now live.</green>');
        return 0;
    }
}
