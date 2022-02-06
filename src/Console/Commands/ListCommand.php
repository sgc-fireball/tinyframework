<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\ConsoleKernelInterface;
use TinyFramework\Console\Input\Argument;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Shell\TabCompletion\TinyFrameworkMatcher;

class ListCommand extends CommandAwesome
{

    private const HEADER = <<<EOF
 _____ _             _____                                            _
|_   _(_)_ __  _   _|  ___| __ __ _ _ __ ___   _____      _____  _ __| | __
  | | | | '_ \| | | | |_ | '__/ _` | '_ ` _ \ / _ \ \ /\ / / _ \| '__| |/ /
  | | | | | | | |_| |  _|| | | (_| | | | | | |  __/\ V  V / (_) | |  |   <
  |_| |_|_| |_|\__, |_|  |_|  \__,_|_| |_| |_|\___| \_/\_/ \___/|_|  |_|\_\
               |___/
EOF;

    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('List all commands')
            ->argument(new Argument('hint', Argument::VALUE_OPTIONAL, 'Filter the command list.'));
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        $kernel = $this->container->get(ConsoleKernelInterface::class);
        $hint = $this->input->argument('hint')->value();
        return $this->outputText($kernel->getCommands(), $hint);
    }

    /**
     * @param CommandAwesome[] $commands
     * @param string|null $hint
     * @return int
     */
    private function outputText(array $commands, string|null $hint = null): int
    {
        if ($hint === null) {
            $this->output->writeln(self::HEADER . PHP_EOL);
            $this->output->writeln("<yellow>Available commands:</yellow>");
        } else {
            $commands = array_filter($commands, function (CommandAwesome $awesome) use ($hint): bool {
                return mb_strpos($awesome->configuration()->name(), $hint) !== false;
            });
        }

        if ($hint) {
            if (count($commands) === 0) {
                $this->output->error(sprintf('Command %s not found.', $hint));
                return 1;
            }
            $this->output->writeln(sprintf("<yellow>Command %s not found. Did you mean?</yellow>", $hint));
        }

        foreach ($commands as $command) {
            $configuration = $command->configuration();
            if ($hint === null || mb_strpos($configuration->name(), $hint) !== false) {
                $this->output->writeln(sprintf(
                    "  <green>%s</green>%s",
                    str_pad($configuration->name(), 32),
                    $configuration->description()
                ));
            }
        }
        return 0;
    }

}
