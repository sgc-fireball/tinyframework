<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use phpDocumentor\Reflection\Types\Integer;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\Components\Table;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Core\ConfigInterface;
use TinyFramework\Helpers\Arr;

class TinyframeworkConfigShowCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Show the configuration')
            ->sections([
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>'
            ]);
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        /** @var ConfigInterface $config */
        $config = $this->container->get('config');

        $table = new Table($output);
        $table->header(['key', 'value']);
        Arr::factory($config->get())
            ->dot()
            ->each(function ($v, $k) use ($table) {
                $v = is_null($v) ? 'NULL' : $v;
                $v = is_bool($v) ? ($v ? 'TRUE' : 'FALSE') : $v;
                $table->row([$k, (string)$v]);
            });
        $table->render();
        return 0;
    }
}
