<?php declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Template\ViewInterface;

class TinyframeworkDownCommand extends CommandAwesome
{

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Describe args behaviors')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('ip', 'i', InputOption::VALUE_OPTIONAL, 'Allowed client', '127.0.0.1'),
                ])
            );
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        parent::run($input, $output);
        $whitelist = $this->input->hasOption('ip') ? $this->input->getOption('ip') : '127.0.0.1';
        $whitelist = is_array($whitelist) ? $whitelist : explode(',', $whitelist);
        file_put_contents('storage/maintenance.json', json_encode(['whitelist' => $whitelist]));
        $this->output->writeln('<comment>Application is now in maintenance mode.</comment>');
        return 0;
    }

}
