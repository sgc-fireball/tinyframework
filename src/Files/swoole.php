#!/usr/bin/env php
<?php

declare(strict_types=1);

use TinyFramework\Core\Container;
use TinyFramework\Http\HttpKernel;
use TinyFramework\Http\SwooleServer;

$path = preg_replace('/\/src\/.*/', '', __DIR__);
$path = preg_replace('/\/vendor\/.*/', '', $path);
define('ROOT', realpath($path));
chdir(ROOT);
umask(0027);

if (fileowner(__FILE__) !== 0) {
    $groupId = filegroup(__FILE__);
    $userId = fileowner(__FILE__);
    printf("Change user to %d:%d.\n", $userId, $groupId);
    posix_setgid($groupId);
    posix_setuid($userId);
}

define('SWOOLE', true);
define('TINYFRAMEWORK_START', microtime(true));
if (!file_exists('vendor/autoload.php')) {
    echo "Please run 'composer install' first.\n";
    exit(1);
}
if (!extension_loaded('swoole')) {
    echo "Please install 'php" . phpversion('tidy') . "-swoole' first.\n";
    exit(2);
}
require_once('vendor/autoload.php');
if (file_exists('vendor/composer/platform_check.php')) {
    require_once('vendor/composer/platform_check.php');
}
if (!extension_loaded('swoole')) {
    echo "Tinyframework detected issues in your platform: missing php-swoole extension\n";
    exit(3);
}
define('TINYFRAMEWORK_START_AUTOLOAD', microtime(true));

// Request::setTrustedProxies(['127.0.0.1', '::1']);
$container = Container::instance();
$kernel = $container->call(HttpKernel::class);
$container->get(SwooleServer::class)->handle();
exit(0);
