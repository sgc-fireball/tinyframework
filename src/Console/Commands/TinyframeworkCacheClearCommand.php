<?php declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TinyFramework\Cache\CacheInterface;
use TinyFramework\Console\CommandAwesome;

class TinyframeworkCacheClearCommand extends CommandAwesome
{

    public function run(InputInterface $input, OutputInterface $output)
    {
        parent::run($input, $output);
        $this->output->write('[<info>....</info>] Cache clear');
        /** @var CacheInterface $cache */
        $cache = $this->container->get('cache');
        $cache->clear();
        $this->output->write("\r[<info>DONE</info>]\n");
        return 0;
    }

}
