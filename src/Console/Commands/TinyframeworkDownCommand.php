<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use RuntimeException;
use TinyFramework\Console\CommandAwesome;
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
            ->option(
                Option::create('ip', null, Option::VALUE_IS_ARRAY, 'Whitelist: one address per option.', ['127.0.0.1'])
            );
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        $whitelist = $this->input->option('ip') ? $this->input->option('ip')->value() : '127.0.0.1';
        $whitelist = is_array($whitelist) ? $whitelist : explode(',', $whitelist);
        $file = storage_dir('/maintenance.json');
        if (file_put_contents($file, json_encode(['whitelist' => $whitelist])) === false) {
            throw new RuntimeException('Could not write maintenance file.');
        }
        if (!chmod($file, 0640)) {
            throw new RuntimeException('Could not set chmod on maintenance file.');
        }
        $this->output->writeln('<gold>Application is now in maintenance mode.</gold>');
        return 0;
    }
}
