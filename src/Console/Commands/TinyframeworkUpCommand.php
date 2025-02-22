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
            ->description('Bring the application out of maintenance mode.')
            ->sections([
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>'
            ]);
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        $file = storage_dir('maintenance.json');
        if (file_exists($file)) {
            @unlink($file);
        }
        $this->output->writeln('<green>Application is now live.</green>');
        return 0;
    }
}
