<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use DateTimeImmutable;
use DateTimeZone;
use phpDocumentor\Reflection\Types\Integer;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Input\Option;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Cron\CronExpression;
use TinyFramework\Cron\CronjobAwesome;
use TinyFramework\Cron\CronjobInterface;
use TinyFramework\Logger\LoggerInterface;

class TinyframeworkCronjobCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Cronjob Service')
            ->sections([
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'WWW' => 'https://github.com/sgc-fireball/tinyframework'
            ])
            ->option(Option::create('list', 'l', Option::VALUE_NONE, 'Prints a list with all cronjobs.'));
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        if ($input->option('list')->value()) {
            return $this->runCronjobList($input, $output);
        }
        return $this->runCronjobs($input, $output);
    }

    private function runCronjobs(InputInterface $input, OutputInterface $output): int
    {
        $timezone = new DateTimeZone($this->container->get('config')->get('app.timezone', 'UTC'));
        $now = new DateTimeImmutable('now', $timezone);
        /** @var LoggerInterface $logger */
        $logger = $this->container->get('logger');
        /** @var CronjobInterface[] $jobs */
        $jobs = array_filter(
            $this->container->tagged('cronjob'),
            function (CronjobInterface|CronjobAwesome $job) use ($now) {
                $cron = new CronExpression($job->expression());
                $mustRun = $cron->isDue($now);
                /** @var int $verbosity */
                if ($mustRun && $job instanceof CronjobAwesome) {
                    $mustRun = !$job->skip();
                }
                $verbosity = $this->output->verbosity();
                if (!$mustRun && $verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->output->writeln(
                        sprintf(
                            "\r[<gold>SKIP</gold>] <yellow>%s</yellow> next: %s",
                            get_class($job),
                            $cron->getNextRunDate()->format('Y-m-d H:i:s')
                        )
                    );
                }
                return $mustRun;
            }
        );

        foreach ($jobs as $job) {
            try {
                $start = microtime(true);
                if ($job instanceof CronjobAwesome) {
                    $this->output->write(sprintf("\r[INIT ] <yellow>%s</yellow>", get_class($job)));
                    $job->onStart();
                }
                $this->output->write(sprintf("\r[RUN ] <yellow>%s</yellow>", get_class($job)));
                $job->handle();
                $this->output->write(
                    sprintf(
                        "\r[<green>DONE</green>] <yellow>%s</yellow> in %.2f secs.\n",
                        get_class($job),
                        microtime(true) - $start
                    )
                );
                if ($job instanceof CronjobAwesome) {
                    $job->onSuccess();
                }
            } catch (\Throwable $e) {
                if ($job instanceof CronjobAwesome) {
                    $job->onFailed();
                }
                $this->output->write(
                    sprintf(
                        "\r[<red>FAIL</red>] <yellow>%s</yellow> in %.2f secs.\n",
                        get_class($job),
                        microtime(true) - $start
                    )
                );
                $logger->error(exception2text($e, true));
            } finally {
                if ($job instanceof CronjobAwesome) {
                    $job->onEnd();
                }
            }
        }
        return 0;
    }

    private function runCronjobList(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('# For information see the manual pages of crontab(5) and cron(8)');
        $output->writeln('#');
        $output->writeln('# m h  dom mon dow   command');
        $output->writeln('');
        foreach ($this->container->tagged('cronjob') as $job) {
            assert($job instanceof CronjobInterface);
            $expression = new CronExpression($job->expression());
            $output->writeln($job->expression() . ' \\' . get_class($job));
            $output->writeln('# next run: ' . $expression->getNextRunDate()->format('Y-m-d H:i:s'));
            $output->writeln('');
        }
        return 0;
    }
}
