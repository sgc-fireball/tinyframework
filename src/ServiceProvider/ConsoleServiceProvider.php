<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Core\ContainerInterface;
use TinyFramework\Cron\LogRotateCronjob;
use TinyFramework\Event\EventDispatcherInterface;
use TinyFramework\Shell\Readline;
use TinyFramework\Shell\Shell;
use TinyFramework\Shell\TabCompletion\ClassTabCompletion;
use TinyFramework\Shell\TabCompletion\ConstantTabCompletion;
use TinyFramework\Shell\TabCompletion\FunctionTabCompletion;
use TinyFramework\Shell\TabCompletion\VariableTabCompletion;
use TinyFramework\System\SignalHandler;

class ConsoleServiceProvider extends ServiceProviderAwesome
{
    public function register(): void
    {
        $this->container->tag('shell:tab:completion', [
            ClassTabCompletion::class,
            ConstantTabCompletion::class,
            FunctionTabCompletion::class,
            VariableTabCompletion::class
        ]);
        $this->container->tag('cronjob', [
            LogRotateCronjob::class
        ]);
        $this->container->singleton(Readline::class, function () {
            return new Readline('$', $this->container->tagged('shell:tab:completion'));
        });
        $this->container->singleton(Shell::class, function () {
            return new Shell(
                $this->container->get(OutputInterface::class),
                $this->container->get(Readline::class),
            );
        });
    }
}
