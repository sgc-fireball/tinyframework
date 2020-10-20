<?php declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Shell\TabCompletion\TinyFrameworkMatcher;
use Psy\Configuration;
use Psy\Shell as PsySh;

class TinyframeworkShellCommand extends CommandAwesome
{

    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Start a psysh.');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);

        $cache = (defined('ROOT') ? ROOT : '.') . '/storage/psysh';
        if (!is_dir($cache)) {
            mkdir($cache, 0700, true);
        }
        $config = new Configuration([
            'updateCheck' => 'never',
            'prompt' => '$ ',
            'commands' => [],
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
