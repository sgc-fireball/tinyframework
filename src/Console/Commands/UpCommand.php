<?php declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Template\ViewInterface;

class UpCommand extends CommandAwesome
{

    public function run(InputInterface $input, OutputInterface $output)
    {
        parent::run($input, $output);
        if (file_exists('storage/maintenance.json')) {
            @unlink('storage/maintenance.json');
        }
        $this->output->writeln('<info>Application is now live.</info>');
        return 0;
    }

}
