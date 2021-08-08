<?php declare(strict_types=1);

namespace TinyFramework\Console\Commands;

use TinyFramework\Console\CommandAwesome;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
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
            ->description('List all routes');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        parent::run($input, $output);
        $table = new Table($this->output);
        $table->header(['Method', 'URL', 'Name']);
        /** @var Router $router */
        $router = container('router');
        $data = [];
        foreach ($router->routes() as $route) {
            /** @var Route $route */
            $data[] = [
                implode('|', $route->method()),
                $route->url() ?: '/',
                $route->name() ?: '',
            ];
        }
        usort($data, fn($a, $b) => ($a[1] < $b[1]) ? -1 : 1);
        $table->rows($data);
        $table->render();
        return 0;
    }

}
