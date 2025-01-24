<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\Argument;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Database\MySQL\Database as MySQLDatabase;

class TinyframeworkDatabaseCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        $connections = implode(
            ', ',
            array_filter(
                array_keys(config('database')),
                function ($connection) {
                    return $connection !== 'default';
                }
            )
        );
        return parent::configure()
            ->description('Starts the system native database client, if supported')
            ->sections([
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>'
            ])
            ->argument(Argument::create('connection', Argument::VALUE_OPTIONAL, $connections, config('database.default')));
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        $connections = config('database');
        $connection = (string)($input->argument('connection')?->value());
        if ($connection === 'default' || !array_key_exists($connection, $connections)) {
            $this->output->error('Invalid connection: ' . $connection);
            return 1;
        }
        $connection = $connections[$connection];
        if ($connection['driver'] === MySQLDatabase::class) {
            return $this->runMySQL($connection);
        }
        $this->output->error('Currently not supported connection: ' . $connection);
        return 2;
    }

    private function getCommand(string $cmd): ?string
    {
        $response = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
        if (!$response) {
            return null;
        }
        $return = trim($response);
        return empty($return) ? null : $return;
    }

    private function runMySQL(array $connection): int
    {
        $binary = $this->getCommand('mysql');
        if (!$binary) {
            $this->output->error('Missing mysql client binary.');
            return 2;
        }
        pcntl_exec($binary, [
            '-h',
            $connection['host'] ?? 'localhost',
            '-P',
            $connection['port'] ?? 3306,
            '-u',
            $connection['username'] ?? 'root',
            '-p' . ($connection['password'] ?? ''),
            $connection['database'] ?? ''
        ]);
        return 0;
    }
}
