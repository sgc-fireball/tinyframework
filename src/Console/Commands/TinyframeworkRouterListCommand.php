<?php

declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Input\Option;
use TinyFramework\Console\Output\Components\Table;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Http\Route;
use TinyFramework\Http\Router;
use TinyFramework\Template\ViewInterface;

class TinyframeworkRouterListCommand extends CommandAwesome
{
    protected function configure(): InputDefinitionInterface
    {
        return parent::configure()
            ->description('List all routes')
            ->sections([
                'AUTHOR' => 'Written by Richard Hülsberg.',
                'EXIT STATUS' => 'The program utility exits 0 on success, and >0 if an error occurs.',
                'BUGS' => 'https://github.com/sgc-fireball/tinyframework/issues',
                'SEE ALSO' => 'Full documentation <https://github.com/sgc-fireball/tinyframework/blob/master/docs/index.md>'
            ])
            ->option(Option::create('middlewares', 'm', Option::VALUE_OPTIONAL | Option::VALUE_NONE, 'Shows the middlewares.'));
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        $withMiddlewares = $input->option('middlewares')->value();

        $header = ['Method', 'URL', 'Name'];
        if ($withMiddlewares) {
            $header[] = 'Middlewares';
        }
        $table = new Table($this->output);
        $table->header($header);

        /** @var Router $router */
        $router = container('router');
        $list = [];
        foreach ($router->routes() as $route) {
            /** @var Route $route */
            $item = [
                implode('|', $route->method()),
                $route->url() ?: '/',
                $route->name() ?: '',
            ];
            if ($withMiddlewares) {
                $item[] = trim(
                    implode(
                        ', ',
                        array_map(
                            fn ($class) => class_basename($class),
                            $route->middleware()
                        )
                    ),
                    ', '
                );
            }
            $list[] = $item;
        }
        usort($list, fn ($a, $b) => ($a[1] < $b[1]) ? -1 : 1);
        $table->rows($list);
        $table->render();
        return 0;
    }
}
