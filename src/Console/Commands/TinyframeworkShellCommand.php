<?php declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use Psy\Configuration;
use Psy\Shell as PsySh;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\ConsoleKernel;
use TinyFramework\Shell\TabCompletion\TinyFrameworkMatcher;

class TinyframeworkShellCommand extends CommandAwesome
{

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Start a psysh');
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        parent::run($input, $output);

        $cache = (defined('ROOT') ? ROOT : '.') . '/storage/psysh';
        if (!is_dir($cache)) {
            mkdir($cache, 0700, true);
        }
        $config = new Configuration([
            'updateCheck' => 'never',
            'prompt' => '$ ',
            'commands' => $this->container->get(ConsoleKernel::class)->getCommands(),
            'runtimeDir' => $cache,
            'startupMessage' => $this->getMotd(),
            'tabCompletionMatchers' => [new TinyFrameworkMatcher()],
        ]);
        $shell = new PsySh($config);
        return $shell->run();
    }

    private function getMotd(): string
    {
        return '';
    }

}
