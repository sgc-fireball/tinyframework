<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Cache\CacheInterface;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Input\Option;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Logger\LoggerInterface;
use TinyFramework\Queue\JobInterface;
use TinyFramework\Queue\QueueInterface;
use TinyFramework\Queue\SyncQueue;
use TinyFramework\System\SignalHandler;

class TinyframeworkQueueWorkerCommand extends CommandAwesome
{
    private float $started;

    public function __construct()
    {
        $this->started = microtime(true);
    }

    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Start processing jobs on the queue as a daemon.')
            ->sections([
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'SEE ALSO' => 'tinyframework:queue:stop',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>'
            ])
            ->option(new Option('queue', null, Option::VALUE_IS_ARRAY, 'Queue name', []));
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);

        /** @var QueueInterface $connection */
        $connection = $this->container->get('queue');
        if ($connection instanceof SyncQueue) {
            return 0;
        }

        $queues = $this->input->option('queue')->value();
        $queues = empty($queues) ? ['default'] : $queues;
        $queues = array_flip($queues);
        foreach ($queues as $queueName => $queue) {
            $queues[$queueName] = $connection->name($queueName);
        }
        $queues = array_values($queues);

        SignalHandler::catchAll();
        /** @var LoggerInterface $logger */
        $logger = $this->container->get('logger');
        while (!$this->isTerminated()) {
            /** @var QueueInterface $queue */
            foreach ($queues as $queue) {
                $job = $queue->pop();
                if ($job instanceof JobInterface) {
                    try {
                        $this->output->write(sprintf('[<yellow>....</yellow>] %s %s', $queue->name(), get_class($job)));
                        $job->handle();
                        $queue->ack($job);
                        $this->output->write("\r[<green>DONE</green>]\n");
                    } catch (\Throwable $e) {
                        $queue->nack($job);
                        $this->output->write("\r[<red>FAIL</red>]\n");
                        $this->output->error($e->getMessage());
                        $logger->error(exception2text($e));
                    }
                    continue 2;
                }
            }
            sleep(1);
        }
        return 0;
    }

    protected function isTerminated(): bool
    {
        /** @var CacheInterface $cache */
        $cache = container('cache');
        $reset_at = $cache->get('workers.queue_stop') ?: 0;
        if ($this->started < $reset_at) {
            $this->output->info('Worker stopped because a restart was requested.');
            return true;
        }
        return parent::isTerminated();
    }
}
