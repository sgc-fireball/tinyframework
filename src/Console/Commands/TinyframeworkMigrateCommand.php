<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use InvalidArgumentException;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\Argument;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Database\MigrationInstaller;

class TinyframeworkMigrateCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Run all database migrations.')
            ->sections([
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>'
            ])
            ->argument(Argument::create('direction', Argument::VALUE_OPTIONAL, 'up or down', 'up'));
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        /** @var MigrationInstaller $migrationInstaller */
        $migrationInstaller = $this->container->get(MigrationInstaller::class);
        $direction = $this->input->argument('direction')->value();
        if (in_array($direction, ['up', 'down'])) {
            $migrationInstaller->{$direction}();
        } else {
            throw new InvalidArgumentException('Please pass a valid direction: up or down');
        }
        return 0;
    }
}
