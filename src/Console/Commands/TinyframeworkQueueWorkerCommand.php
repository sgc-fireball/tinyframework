<?php declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Logger\LoggerInterface;
use TinyFramework\Queue\JobInterface;
use TinyFramework\Queue\QueueInterface;
use TinyFramework\Queue\SyncQueue;

class TinyframeworkQueueWorkerCommand extends CommandAwesome
{

    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Start processing jobs on the queue as a daemon.');
    }

    /**
     * @TODO implement a signal handler!
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);

        /** @var QueueInterface $queue */
        $queue = $this->container->get('queue');
        if ($queue instanceof SyncQueue) {
            return 0;
        }
        /** @var LoggerInterface $logger */
        $logger = $this->container->get('logger');
        while (true) {
            $job = $queue->pop(10);
            if ($job instanceof JobInterface) {
                try {
                    $job->handle();
                } catch (\Throwable $e) {
                    $logger->error(exception2text($e));
                }
            }
        }
        return 0;
    }

}
