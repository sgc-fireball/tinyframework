<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Input\Option;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Shell\Shell;
use TinyFramework\Shell\TabCompletion\TinyFrameworkMatcher;

class TinyframeworkServeCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Start a webserver.')
            ->sections([
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>'
            ])
            ->option(Option::create('host', null, Option::VALUE_OPTIONAL, 'listen host', '0.0.0.0'))
            ->option(Option::create('port', 'p', Option::VALUE_OPTIONAL, 'listen port', 8000));
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        $cmd = PHP_BINARY;
        $host = $this->input->option('host')->value();
        $port = (int)$this->input->option('port')->value();
        $root = root_dir() . '/public';
        passthru(sprintf('%s -S %s:%d -t %s -d variables_order=EGPCS', $cmd, $host, $port, $root));
        return 0;
    }
}
