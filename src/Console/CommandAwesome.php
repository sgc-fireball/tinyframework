<?php declare(strict_types=1);

namespace TinyFramework\Console;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TinyFramework\Core\Container;
use TinyFramework\Core\ContainerInterface;

abstract class CommandAwesome extends BaseCommand implements CommandInterface
{

    protected ContainerInterface $container;

    protected InputInterface $input;

    protected OutputInterface $output;

    public function __construct(string $name = null)
    {
        $name = $name ?: static::getDefaultName();
        parent::__construct($name);
    }

    public static function getDefaultName()
    {
        $command = str_replace('\\', '/', static::class);
        if (mb_strpos($command, '/') !== false) {
            $command = basename($command);
        }
        $command = preg_replace('/Command$/', '', $command);
        $command = preg_replace_callback('/([A-Z])/', function ($match) {
            return ':' . mb_strtolower($match[0]);
        }, $command);
        return ltrim($command, ':');
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->container = Container::instance();
        // @TODO compile inputargs with definition
        return 0;
    }

}
