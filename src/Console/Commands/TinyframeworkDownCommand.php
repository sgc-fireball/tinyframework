<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\Argument;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Input\Option;
use TinyFramework\Console\Output\OutputInterface;

class TinyframeworkDownCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Put the application into maintenance mode.')
            ->option(Option::create('ip', null, Option::VALUE_IS_ARRAY, 'Whitelist: one address per option.', ['127.0.0.1']));
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        $whitelist = $this->input->option('ip') ? $this->input->option('ip')->value() : '127.0.0.1';
        $whitelist = is_array($whitelist) ? $whitelist : explode(',', $whitelist);
        file_put_contents('storage/maintenance.json', json_encode(['whitelist' => $whitelist]));
        $this->output->writeln('<gold>Application is now in maintenance mode.</gold>');
        return 0;
    }
}
