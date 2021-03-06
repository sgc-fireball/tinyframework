<?php declare(strict_types=1);

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
            ->option(Option::create('host', null, Option::VALUE_OPTIONAL, 'listen host', '0.0.0.0'))
            ->option(Option::create('port', 'p', Option::VALUE_OPTIONAL, 'listen port', 8000));
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        $cmd = $_SERVER['_'];
        $host = $this->input->option('host')->value();
        $port = (int)$this->input->option('port')->value();
        $root = root_dir() . '/public';
        passthru(sprintf('%s -S %s:%d -f %s', $cmd, $host, $port, $root));
        return 0;
    }

}
