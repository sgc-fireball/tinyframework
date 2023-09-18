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

        $this->output->write('[<green>....</green>] Try to install as: ' . $name);
        if (!file_put_contents($path, $this->getSystemdServiceFile($input))) {
            $this->output->write("\r[<red>FAIL</red>]\n");
            return 1;
        }
        $this->output->write("\r[<green>DONE</green>]\n");

        shell_exec('systemctl daemon-reload');
        shell_exec('systemctl enable ' . escapeshellarg($name));
        shell_exec('systemctl restart ' . escapeshellarg($name));
        return 0;
    }

    private function getSystemdServiceFile(InputInterface $input): string
    {
        $name = (string)$input->option('name')->value();
        $user = (string)$input->option('user')->value();
        $group = (string)$input->option('group')->value();
        $root = root_dir();
        $binary = $root . DIRECTORY_SEPARATOR . 'swoole.php';
        return <<<EOF
[Unit]
Description=${$name} Swoole Service
After=network.target

[Service]
Type=forking
User=${user}
Group=${group}
Environment="SWOOLE_DAEMONIZE=1"
WorkingDirectory=${root}
ExecStart=php ${binary}
ExecStop=/bin/kill -TERM \$MAINPID
ExecReload=/bin/kill -USR1 \$MAINPID
PIDFile=${root}/storage/shell/swoole.pid

[Install]
WantedBy = multi-user.target
EOF;
    }
}
