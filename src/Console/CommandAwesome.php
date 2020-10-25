<?php declare(strict_types=1);

namespace TinyFramework\Console;

use TinyFramework\Console\Input\InputDefinition;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Input\Option;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Core\Container;
use TinyFramework\Core\ContainerInterface;
use TinyFramework\System\SignalHandler;

abstract class CommandAwesome
{

    private ?InputDefinitionInterface $configure = null;
    protected ?InputInterface $input;
    protected ?OutputInterface $output;
    protected ?ContainerInterface $container;

    protected function configure(): InputDefinitionInterface
    {
        $command = str_replace('\\', '/', static::class);
        if (mb_strpos($command, '/') !== false) {
            $command = basename($command);
        }
        $command = preg_replace('/Command$/', '', $command);
        $command = preg_replace_callback('/([A-Z])/', function ($match) {
            return ':' . mb_strtolower($match[0]);
        }, $command);
        return InputDefinition::create(ltrim($command, ':'))
            ->option(Option::create('help', 'h', null, 'Print the help message.'))
            ->option(Option::create('quiet', 'q', null, 'Do not output any message'))
            ->option(Option::create('verbose', 'v', null, 'Increase the verbose level.'))
            ->option(Option::create('ansi', null, null, 'Force ANSI output'))
            ->option(Option::create('no-ansi', null, null, 'Disable ANSI output'))
            ->option(Option::create('no-interaction', 'n', null, 'Do not ask any interactive question.'));
    }

    /**
     * @internal
     */
    public function configuration(): InputDefinitionInterface
    {
        if (is_null($this->configure)) {
            $this->configure = $this->configure();
        }
        return $this->configure;
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->container = Container::instance();
        return 0;
    }

    protected function isTerminated(): bool
    {
        return SignalHandler::isTerminated();
    }

}
