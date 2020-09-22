<?php declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Session\SessionInterface;

class TinyframeworkSessionClearCommand extends CommandAwesome
{

    public function run(InputInterface $input, OutputInterface $output)
    {
        parent::run($input, $output);
        $this->output->write('[<info>....</info>] Session clear');
        /** @var SessionInterface $session */
        $session = $this->container->get('session');
        $session->clear();
        $this->output->write("\r[<info>DONE</info>]\n");
        return 0;
    }

}
