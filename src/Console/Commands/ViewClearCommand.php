<?php declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Template\ViewInterface;

class ViewClearCommand extends CommandAwesome
{

    public function run(InputInterface $input, OutputInterface $output)
    {
        parent::run($input, $output);
        $this->output->write('[<info>....</info>] View clear');
        /** @var ViewInterface $session */
        $session = $this->container->get('view');
        $session->clear();
        $this->output->write("\r[<info>DONE</info>]\n");
        return 0;
    }

}
