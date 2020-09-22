<?php declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Logger\LoggerInterface;
use TinyFramework\Queue\JobInterface;
use TinyFramework\Queue\QueueInterface;
use TinyFramework\Queue\SyncQueue;
use TinyFramework\Session\SessionInterface;
use TinyFramework\Template\ViewInterface;

class TinyframeworkQueueWorkerCommand extends CommandAwesome
{

    public function run(InputInterface $input, OutputInterface $output)
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
                    $job->handle();;
                } catch (\Throwable $e) {
                    $logger->error(exception2text($e));
                }
            }
        }
        return 0;
    }

}
