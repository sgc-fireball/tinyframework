<?php declare(strict_types=1);

use TinyFramework\Core\Container;
use TinyFramework\Console\ConsoleKernel;
use TinyFramework\Core\DotEnv;
use TinyFramework\Core\DotEnvInterface;

define('TINYFRAMEWORK_START', microtime(true));
define('ROOT', __DIR__);
chdir(ROOT);
require_once('vendor/autoload.php');

$container = Container::instance()->singleton(DotEnvInterface::class, DotEnv::class);
$kernel = $container->get(ConsoleKernel::class);
exit($container->call([$kernel, 'handle']));
