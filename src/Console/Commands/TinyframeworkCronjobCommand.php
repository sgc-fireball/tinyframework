<?php declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use DateTimeImmutable;
use DateTimeZone;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Cron\CronExpression;
use TinyFramework\Cron\CronjobInterface;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Logger\LoggerInterface;

class TinyframeworkCronjobCommand extends CommandAwesome
{

    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Cronjob Service');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);

        $timezone = new DateTimeZone($this->container->get('config')->get('app.timezone', 'UTC'));
        $now = new DateTimeImmutable('now', $timezone);
        /** @var LoggerInterface $logger */
        $logger = $this->container->get('logger');
        /** @var CronjobInterface[] $jobs */
        $jobs = array_filter(
            $this->container->tagged('cronjob'),
            function (CronjobInterface $job) use ($now) {
                $cron = new CronExpression($job->expression());
                $mustRun = $cron->isDue($now);
                /** @var int $verbosity */
                $verbosity = $this->output->verbosity();
                if (!$mustRun && $verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->output->writeln(sprintf(
                        "\r[<gold>SKIP</gold>] <yellow>%s</yellow> next: %s",
                        get_class($job),
                        $cron->getNextRunDate()->format('Y-m-d H:i:s')
                    ));
                }
                return $mustRun;
            }
        );

        foreach ($jobs as $job) {
            $start = microtime(true);
            try {
                $this->output->write(sprintf("\r[....] <yellow>%s</yellow>", get_class($job)));
                $job->handle();
                $this->output->write(sprintf(
                    "\r[<green>DONE</green>] <yellow>%s</yellow> in %.2f secs.\n",
                    get_class($job),
                    microtime(true) - $start
                ));
            } catch (\Throwable $e) {
                $this->output->write(sprintf(
                    "\r[<red>FAIL</red>] <yellow>%s</yellow> in %.2f secs.\n",
                    get_class($job),
                    microtime(true) - $start
                ));
                $logger->error(exception2text($e, true));
            }
        }

        return 0;
    }

}
