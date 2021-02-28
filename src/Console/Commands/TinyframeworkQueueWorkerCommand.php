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
use TinyFramework\System\SignalHandler;

class TinyframeworkQueueWorkerCommand extends CommandAwesome
{

    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Start processing jobs on the queue as a daemon.');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);

        /** @var QueueInterface $queue */
        $queue = $this->container->get('queue');
        if ($queue instanceof SyncQueue) {
            return 0;
        }

        SignalHandler::catchAll();
        /** @var LoggerInterface $logger */
        $logger = $this->container->get('logger');
        while (!$this->isTerminated()) {
            $job = $queue->pop(3);
            if ($job instanceof JobInterface) {
                try {
                    $this->output->write(sprintf('[<yellow>....</yellow>] %s', get_class($job)));
                    $job->handle();
                    $this->output->write("\r[<green>DONE</green>]\n");
                } catch (\Throwable $e) {
                    $this->output->write("\r[<red>FAIL</red>]\n");
                    $this->output->error($e->getMessage());
                    $logger->error(exception2text($e));
                }
            }
        }
        return 0;
    }

}
