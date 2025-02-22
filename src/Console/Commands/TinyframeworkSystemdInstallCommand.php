<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Input\Option;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Helpers\Str;

class TinyframeworkSystemdInstallCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Installing swoole server into systemd services.')
            ->sections([
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>',
            ])
            ->option(Option::create('name', 'n', Option::VALUE_REQUIRED, 'The systemd service name.', 'tinyframework'))
            ->option(Option::create('user', 'u', Option::VALUE_REQUIRED, 'The systemd service username.', 'www-data'))
            ->option(Option::create('group', 'g', Option::VALUE_REQUIRED, 'The systemd service group.', 'www-data'));
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);

        if (posix_getuid() !== 0) {
            $command = sprintf(
                'sudo "%s" "%s" "%s" --name "%s" --user "%s" --group "%s"',
                PHP_BINARY,
                root_dir() . '/console',
                $this->configuration()->name(),
                escapeshellarg((string)$input->option('name')->value()),
                escapeshellarg((string)$input->option('user')->value()),
                escapeshellarg((string)$input->option('group')->value()),
            );
            $this->output->write('<orange>Restart as root: ' . $command . '<orange>');
            system($command, $exitCode);
            return $exitCode;
        }

        $name = Str::factory((string)$input->option('name')->value())->slug()->toString();
        $path = '/etc/systemd/system/' . $name . '.service';

        $this->output->write('[<green>....</green>] Write: ' . $path);
        if (!file_put_contents($path, $this->getSystemdServiceFile($input))) {
            $this->output->write("\r[<red>FAIL</red>]\n");
            return 1;
        }
        $this->output->write("\r[<green>DONE</green>]\n");

        $output = [];
        $exitCode = 0;
        $this->execute('systemctl daemon-reload', $output, $exitCode);
        $this->execute('systemctl enable ' . escapeshellarg($name), $output, $exitCode);
        $this->execute('systemctl restart ' . escapeshellarg($name), $output, $exitCode);

        return 0;
    }

    private function getSystemdServiceFile(InputInterface $input): string
    {
        $name = (string)$input->option('name')->value();
        $user = (string)$input->option('user')->value();
        $group = (string)$input->option('group')->value();
        $root = root_dir();
        $binary = $root . DIRECTORY_SEPARATOR . 'console --swoole';

        $content = [];
        $content[] = '[Unit]';
        $content[] = 'Description=' . $name . ' Swoole Service';
        $content[] = 'After=network.target';
        $content[] = '';
        $content[] = '[Service]';
        $content[] = 'Type=simple';
        $content[] = 'User=' . $user;
        $content[] = 'Group=' . $group;
        $content[] = 'Environment="SWOOLE_DAEMONIZE=1"';
        $content[] = 'WorkingDirectory=' . $root;
        $content[] = 'ExecStart=/usr/bin/php ' . $binary;
        $content[] = 'ExecStop=/bin/kill -s KILL $MAINPID';
        $content[] = 'ExecReload=/bin/kill -s HUP $MAINPID';
        $content[] = 'KillMode=control-group';
        $content[] = 'PIDFile=' . $root . '/storage/shell/swoole.pid';
        $content[] = '';
        $content[] = '[Install]';
        $content[] = 'WantedBy=multi-user.target';
        $content[] = '';
        return implode("\n", $content);
    }

    private function execute(string $command, array &$output = [], int &$result_code = 0): string|false
    {
        $this->output->write('[<green>....</green>] Run: ' . $command);
        $result = exec($command, $output, $result_code);
        if ($result_code) {
            $this->output->write("\r[<red>FAIL</red>]\n");
            return $result;
        }
        $this->output->write("\r[<green>DONE</green>]\n");
        return $result;
    }

}
