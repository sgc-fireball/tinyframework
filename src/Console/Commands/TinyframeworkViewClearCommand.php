<?php declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Template\ViewInterface;

class TinyframeworkViewClearCommand extends CommandAwesome
{

    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Clear all compiled view files');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        $this->output->write('[<green>....</green>] View clear');
        /** @var ViewInterface $session */
        $session = $this->container->get('view');
        $session->clear();
        $this->output->write("\r[<green>DONE</green>]\n");
        return 0;
    }

}
