<?php declare(strict_types=1);

namespace TinyFramework\Console;

use TinyFramework\Console\Input\InputDefinition;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
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
        return InputDefinition::create(ltrim($command, ':'));
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

        if ($this->output->verbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            if ($this->container->get('kernel')->inMaintenanceMode()) {
                $this->output->warning('Running in maintenance mode.');
            }
        }
        return 0;
    }

    protected function isTerminated(): bool
    {
        return SignalHandler::isTerminated();
    }

}
