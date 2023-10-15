#!/usr/bin/env php
<?php

declare(strict_types=1);

use TinyFramework\Console\ConsoleKernel;
use TinyFramework\Core\Container;
use TinyFramework\Http\HttpKernel;
use TinyFramework\Http\SwooleServer;

define('PHARBIN', str_starts_with(__DIR__, 'phar://'));
define('TINYFRAMEWORK_START', microtime(true));
if (function_exists('pcntl_async_signals')) {
    pcntl_async_signals(true);
} else {
    declare(ticks=10);
}

$path = __DIR__;
$path = preg_replace('/\/src\/.*/', '', $path);
$path = preg_replace('/\/vendor\/.*/', '', $path);
define('ROOT', PHARBIN ? $path : realpath($path));
if (!PHARBIN) {
    chdir(ROOT);
}
umask(0027);
define('SWOOLE', extension_loaded('swoole'));

/*if (fileowner(__FILE__) !== 0) {
    $groupId = filegroup(__FILE__);
    $userId = fileowner(__FILE__);
    printf("Change user to %d:%d.\n", $userId, $groupId);
    posix_setgid($groupId);
    posix_setuid($userId);
}*/

if (!file_exists('vendor/autoload.php')) {
    echo "Please run 'composer install' first.\n";
    exit(1);
}
require_once('vendor/autoload.php');
if (file_exists('vendor/composer/platform_check.php')) {
    require_once('vendor/composer/platform_check.php');
}
define('TINYFRAMEWORK_START_AUTOLOAD', microtime(true));

$container = Container::instance();
if (in_array('--swoole', $_SERVER['argv'])) {
    if (!SWOOLE) {
        echo "Please install 'php" . phpversion('tidy') . "-swoole' first.\n";
        exit(2);
    }
    // Request::setTrustedProxies(['127.0.0.1', '::1']);
    $kernel = $container->call(HttpKernel::class);
    $container->get(SwooleServer::class)->handle();
    exit(0);
}

$kernel = $container->call(ConsoleKernel::class);
exit($container->call([$kernel, 'handle']));
