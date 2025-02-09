<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Cache\CacheInterface;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Input\Option;
use TinyFramework\Console\Output\OutputInterface;

class TinyframeworkCacheClearCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Flush the application cache.')
            ->option(
                new Option(
                    'queue-worker-restart',
                    'r',
                    Option::VALUE_NONE,
                    'Automatically restart the queue worker.',
                    false
                )
            )
            ->sections([
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>',
            ]);
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);

        /**
         * Clear normal cache
         */
        $this->output->write('[<green>....</green>] Clear cache');
        /** @var CacheInterface $cache */
        $cache = $this->container->get('cache');
        $cache->clear();
        $this->output->write("\r[<green>DONE</green>]\n");

        /**
         * Restart Queue Worker
         */
        $this->output->write("\n");
        $exitCode = 0;
        if ($input->option('queue-worker-restart')->value()) {
            $command = [
                PHP_BINARY,
                $_SERVER['argv'][0],
                'tinyframework:queue:worker:stop',
            ];
            system(implode(' ', $command), $exitCode);
        } else {
            $this->output->info(
                "We want to recommend, a queue worker restart, with: tinyframework:queue:worker:stop"
            );
        }

        return $exitCode;
    }

    private function clearFile(string $title, string $path): void
    {
        $this->output->write('[<green>....</green>] ' . $title);
        if (file_exists($path)) {
            if (unlink($path)) {
                $this->output->write("\r[<green>DONE</green>]\n");
            } else {
                $this->output->write("\r[<red>FAIL</red>]\n");
            }
        } else {
            $this->output->write("\r[<yellow>DONE</yellow>]\n");
        }
    }

}
