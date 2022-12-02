#!/usr/bin/env php
<?php

declare(strict_types=1);

use TinyFramework\Console\ConsoleKernel;
use TinyFramework\Core\Container;

define('TINYFRAMEWORK_START', microtime(true));
if (file_exists('../../vendor/')) {
    chdir('../..');
}
require_once('vendor/autoload.php');
if (file_exists('vendor/composer/platform_check.php')) {
    require_once('vendor/composer/platform_check.php');
}
define('TINYFRAMEWORK_START_AUTOLOAD', microtime(true));

define('ROOT', realpath(__DIR__));
chdir(ROOT);
if (function_exists('pcntl_async_signals')) {
    pcntl_async_signals(true);
} else {
    declare(ticks=10);
}

$container = Container::instance();
$kernel = $container->call(ConsoleKernel::class);
exit($container->call([$kernel, 'handle']));
