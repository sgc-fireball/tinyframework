<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Cache\CacheInterface;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;

class TinyframeworkCacheClearCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Flush the application cache.')
            ->sections([
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'SEE ALSO' => 'tinyframework:session:clear',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>',
            ]);
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        $this->output->write('[<green>....</green>] Cache clear');
        /** @var CacheInterface $cache */
        $cache = $this->container->get('cache');
        $cache->clear();
        $this->output->write("\r[<green>DONE</green>]\n");
        return 0;
    }
}
