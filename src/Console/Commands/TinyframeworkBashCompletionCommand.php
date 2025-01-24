<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\ConsoleKernelInterface;
use TinyFramework\Console\Input\Input;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;

class TinyframeworkBashCompletionCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        $bin = str_starts_with($_SERVER['SCRIPT_FILENAME'], DIRECTORY_SEPARATOR)
            ? $_SERVER['SCRIPT_FILENAME']
            : $_SERVER['PWD'] . DIRECTORY_SEPARATOR . $_SERVER['SCRIPT_FILENAME'];
        $bin = realpath($bin);

        $install = [];
        $install[] = 'To install, run the following command:';
        $install[] = sprintf(
            '<yellow>%s %s tinyframework:bash:completion | sudo tee /etc/bash_completion.d/tinyframework > /dev/null</yellow>',
            PHP_BINARY,
            $bin
        );
        return parent::configure()
            ->description('Returns the bash completion script for the console commands.')
            ->sections([
                'INSTALL' => implode("\n\t", $install),
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>',
            ]);
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        if (array_key_exists('COMP_LINE', $_SERVER)) {
            return $this->autocomplete();
        }
        return $this->script();
    }

    private function autocomplete(): int
    {
        $argv = explode(' ', $_SERVER['COMP_LINE']);
        $componentInput = new Input($argv);
        $hint = \array_key_exists(0, $componentInput->argv()) ? $componentInput->argv()[0] : null;

        /** @var ConsoleKernelInterface $kernel */
        $kernel = $this->container->get(ConsoleKernelInterface::class);
        $commands = $kernel->getCommands();

        if (!\array_key_exists($hint, $commands)) {
            foreach ($commands as $command) {
                if (!$hint || str_starts_with($command->configuration()->name(), $hint)) {
                    $this->output->writeln($command->configuration()->name() . ' ');
                }
            }
            return 0;
        }

        /** @var InputDefinitionInterface $inputDefinition */
        $inputDefinition = $commands[$hint]->configuration();
        $componentInput->inputDefinition($inputDefinition);
        foreach ($componentInput->completion() as $str) {
            $this->output->writeln($str . ' ');
        }

        return 0;
    }

    private function script(): int
    {
        $bin = str_starts_with($_SERVER['SCRIPT_FILENAME'], DIRECTORY_SEPARATOR)
            ? $_SERVER['SCRIPT_FILENAME']
            : $_SERVER['PWD'] . DIRECTORY_SEPARATOR . $_SERVER['SCRIPT_FILENAME'];
        $bin = realpath($bin);

        // composer completion bash
        // composer _complete --no-interaction -sbash -i "composer up" -s "up" -S2.6.5

        // COMP_LINE="/app/src/Files/console.php tinyframework:" /app/src/Files/console.php tinyframework:bash:completion --no-interaction --no-ansi
        // COMP_LINE="/app/src/Files/console.php tinyframework:cronjob -" /app/src/Files/console.php tinyframework:bash:completion --no-interaction --no-ansi

        $output = [];
        $output[] = '_tinyframework() {';
        $output[] = '  # Use newline as only separator to allow space in completion values';
        $output[] = '  IFS=$\'\\n\'';
        $output[] = '  COMPREPLY=( $(COMP_LINE="${COMP_LINE}" ' . $bin . ' tinyframework:bash:completion --no-interaction --no-ansi) )';
        $output[] = '  return 0';
        $output[] = '}';
        $output[] = '';
        $output[] = 'complete -F _tinyframework -o bashdefault -o default ' . $bin;
        $output[] = '';
        $this->output->write(implode("\n", $output));
        return 0;
    }
}
