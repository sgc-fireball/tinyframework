<?php declare(strict_types=1);

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

    public function register()
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
        $this->container->singleton(Readline::class, function (ContainerInterface $container) {
            return new Readline('$', $container->tagged('shell:tab:completion'));
        });
        $this->container->singleton(Shell::class, function (ContainerInterface $container) {
            return new Shell(
                $container->get(OutputInterface::class),
                $container->get(Readline::class),
            );
        });
    }

}
