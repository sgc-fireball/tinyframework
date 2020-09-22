<?php declare(strict_types=1);

use TinyFramework\Core\Container;
use TinyFramework\Http\HttpKernel;
use TinyFramework\Http\Request;

define('TINYFRAMEWORK_START', microtime(true));
define('ROOT', dirname(__DIR__));
chdir(ROOT);
require_once('vendor/autoload.php');

$httpKernel = Container::instance()->get(HttpKernel::class);
$response = $httpKernel->handle($request = Request::fromGlobal());
$httpKernel->terminate($request, $response->send());
