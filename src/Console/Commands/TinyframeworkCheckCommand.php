<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Cache\CacheInterface;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;

class TinyframeworkCheckCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Check system configuration.')
            ->sections([
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'WWW' => 'https://github.com/sgc-fireball/tinyframework',
            ]);
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        $this->checkSetting('date.timezone', ['UTC']);
        $this->checkSetting('error_reporting', [E_ALL, 'E_ALL']);
        $this->checkSetting('report_memleaks', ['On', '1', 1, 'true']);
        $this->checkSetting('ignore_user_abort', [0]);
        $this->checkSetting('ignore_repeated_errors', ['Off', '', 0, '0', 'false', false, null]);
        $this->checkSetting('allow_url_open', ['Off', '', 0, '0', 'false', false, null]);
        $this->checkSetting('allow_url_include', ['Off', '', 0, '0', 'false', false, null]);
        $this->checkSetting('expose_php', ['Off', '', 0, '0', 'false', false, null]);
        $this->checkSetting('display_errors', ['Off', '', 0, '0', 'false', false, null]);
        $this->checkSetting('display_startup_errors', ['Off', '', 0, '0', 'false', false, null]);
        $this->checkSetting('variables_order', ['EGPCS', 'ES']);
        $this->checkSetting('request_order', ['GP']);
        return 0;
    }

    private function checkSetting(string $option, array $valid, array $warn = []): void
    {
        $this->output->write('[....] Checking ' . $option);
        $value = ini_get($option);
        if ($valid && in_array($value, $valid)) {
            $this->output->write("\r[<green>DONE</green>]\n");
        } elseif ($warn && in_array($value, $warn)) {
            $this->output->write("\r[<yellow>FAIL</yellow>]\n");
        } else {
            $this->output->write("\r[<red>FAIL</red>]\n");
        }
    }
}
