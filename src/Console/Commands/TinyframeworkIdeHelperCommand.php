<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Http\Request;

class TinyframeworkIdeHelperCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('Generate the IDE Helper.')
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
        $this->output->write('[<green>....</green>] IDE Helper');
        $this->createIdeHelper();
        $this->output->write("\r[<green>DONE</green>]\n");
        return 0;
    }

    private function createIdeHelper(): void
    {
        /** @link https://www.jetbrains.com/help/phpstorm/ide-advanced-metadata.html */
        $mappings = ['request' => Request::class];
        $containerReflection = new \ReflectionClass($this->container);

        $aliasesPropertyReflection = $containerReflection->getProperty('aliases');
        $aliasesPropertyReflection->setAccessible(true);
        foreach ($aliasesPropertyReflection->getValue($this->container) as $key => $target) {
            try {
                $target = null;
                $target = $this->container->get($key);
                if ($target && is_object($target)) {
                    $mappings[$key] = get_class($target);
                }
            } catch (\Throwable $e) {
                if ($this->output->verbosity()) {
                    $this->output->error('Error on alias: ' . $key);
                }
            }
        }

        $instancesPropertyReflection = $containerReflection->getProperty('instances');
        $instancesPropertyReflection->setAccessible(true);
        foreach ($instancesPropertyReflection->getValue($this->container) as $key => $instance) {
            try {
                $target = null;
                $target = $this->container->get($key);
                if ($target && is_object($target)) {
                    $mappings[$key] = get_class($target);
                }
            } catch (\Throwable $e) {
                if ($this->output->verbosity()) {
                    $this->output->error('Error on instance: ' . $key);
                }
            }
        }

        ksort($mappings);

        $content = [];
        $content[] = '<?php';
        $content[] = '// @formatter:off';
        $content[] = '';
        $content[] = 'namespace PHPSTORM_META {';
        $content[] = '';
        $content[] = '    /**';
        $content[] = '     * PhpStorm Meta file, to provide autocomplete information for PhpStorm';
        $content[] = '     *';
        $content[] = '     * @author Richard Huelsberg <rh+github@hrdns.de>';
        $content[] = '     * @since ' . date('Y-m-d H:i:s');
        $content[] = '     * @link https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata';
        $content[] = '     */';
        $content[] = '    overwrite(\\container(0), map([';
        $content[] = '        \'\' => \\TinyFramework\\Core\\Container::class,';
        foreach ($mappings as $key => $class) {
            $content[] = sprintf('        \'%s\' => \\%s::class,', $key, ltrim($class, '\\'));
        }
        $content[] = '    ]));';
        $content[] = '    overwrite(\\TinyFramework\\Core\\Container::get(0), map([';
        foreach ($mappings as $key => $class) {
            $content[] = sprintf('        \'%s\' => \\%s::class,', $key, ltrim($class, '\\'));
        }
        $content[] = '    ]));';
        $content[] = '}';
        $content[] = '';

        // expectedArguments(
        //        \Symfony\Component\Console\Command\Command::addArgument(),
        //        1,
        //        \Symfony\Component\Console\Input\InputArgument::OPTIONAL,
        //        \Symfony\Component\Console\Input\InputArgument::REQUIRED,
        //        \Symfony\Component\Console\Input\InputArgument::IS_ARRAY
        //);

        // exitPoint(Application::terminate('bar'));

        $file = root_dir() . '/.phpstorm.meta.php';
        file_put_contents($file, implode(PHP_EOL, $content));
        chmod($file, 0640);
    }
}
