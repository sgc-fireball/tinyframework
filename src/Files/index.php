<?php declare(strict_types=1);

use TinyFramework\Core\Container;
use TinyFramework\Http\HttpKernel;
use TinyFramework\Http\Request;
use TinyFramework\Core\DotEnv;
use TinyFramework\Core\DotEnvInterface;

define('TINYFRAMEWORK_START', microtime(true));
define('ROOT', dirname(__DIR__));
chdir(ROOT);
require_once('vendor/autoload.php');

$container = Container::instance()->singleton(DotEnvInterface::class, DotEnv::class);
$kernel = $container->get(HttpKernel::class);
$container->call([$kernel, 'handle'], ['request' => Request::fromGlobal()]);
