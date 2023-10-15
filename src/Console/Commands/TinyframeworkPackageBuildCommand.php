<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use Phar;
use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Input\Option;
use TinyFramework\Console\Output\OutputInterface;

class TinyframeworkPackageBuildCommand extends CommandAwesome
{

    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Build the hole application into a phar file.')
            ->option(
                Option::create(
                    'file',
                    'f',
                    Option::VALUE_REQUIRED,
                    'Application binary name: tinyframework.phar',
                    root_dir() . '/tinyframework.phar'
                )
            );
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);

        if (!class_exists(Phar::class)) {
            $this->output->error('Error: Missing ext-phar. Please install ext-phar first!');
            return 1;
        }

        $file = $input->option('file')->value();
        if (intval(ini_get('phar.readonly')) === 1) {
            $command = sprintf(
                '%s -d phar.readonly=0 %s %s -n -f %s',
                PHP_BINARY,
                $_SERVER['PHP_SELF'],
                $this->configuration()->name(),
                $file
            );
            $this->output->info('Forward: ' . $command);
            passthru($command, $exitCode);
            return $exitCode;
        }

        $this->cleanup($file);
        $this->build($file);
        $this->output->successful('Build successful: ' . $file . '');
        return 0;
    }

    private function cleanup(string $file): void
    {
        if (!is_dir(dirname($file))) {
            mkdir($file);
        }
        if (file_exists($file)) {
            unlink($file);
        }
    }

    private function build(string $file): void
    {
        // create phar
        $phar = new Phar($file);

        // start buffering. Mandatory to modify stub to add shebang
        $phar->startBuffering();

        // Create the default stub from main.php entrypoint
        if (file_exists(root_dir() . '/console')) {
            $defaultStub = $phar->createDefaultStub('console');
        } else {
            $defaultStub = $phar->createDefaultStub('src/Files/console.php');
        }

        // Add the rest of the apps files
        $phar->buildFromDirectory(root_dir());

        // Customize the stub to add the shebang
        $stub = "#!/usr/bin/env php \n" . $defaultStub;

        // Add the stub
        $phar->setStub($stub);

        $phar->stopBuffering();

        // plus - compressing it into gzip
        $phar->compressFiles(Phar::GZ);

        // Make the file executable
        chmod($file, 0770);
    }
}
