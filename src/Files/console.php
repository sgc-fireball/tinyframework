<?php declare(strict_types=1);

if (function_exists('pcntl_async_signals')) {
    pcntl_async_signals(true);
} else {
    declare (ticks = 10);
}

use TinyFramework\Core\Container;
use TinyFramework\Console\ConsoleKernel;
use TinyFramework\Core\DotEnv;
use TinyFramework\Core\DotEnvInterface;

define('TINYFRAMEWORK_START', microtime(true));
define('ROOT', __DIR__);
chdir(ROOT);
require_once('vendor/autoload.php');
if (file_exists('vendor/composer/platform_check.php')) {
    require_once('vendor/composer/platform_check.php');
}

$container = Container::instance()->singleton(DotEnvInterface::class, DotEnv::class);
$kernel = $container->get(ConsoleKernel::class);
exit($container->call([$kernel, 'handle']));
