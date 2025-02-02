<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Cache\CacheInterface;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;

class TinyframeworkQueueWorkerStopCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Stop queue worker after the current job.')
            ->sections([
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'SEE ALSO' => 'tinyframework:queue:worker',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>'
            ]);
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);

        /** @var CacheInterface $cache */
        $cache = container('cache');
        $cache->set('workers.queue_stop', microtime(true));
        $output->successful('Signal successfully sent to stop any running workers.');

        return 0;
    }
}
