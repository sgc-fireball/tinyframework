<?php declare(strict_types=1);

use TinyFramework\Core\Container;
use TinyFramework\Http\HttpKernel;
use TinyFramework\Http\Request;

define('TINYFRAMEWORK_START', microtime(true));
require_once('vendor/autoload.php');
if (file_exists('vendor/composer/platform_check.php')) {
    require_once('vendor/composer/platform_check.php');
}
define('TINYFRAMEWORK_START_AUTOLOAD', microtime(true));

define('ROOT', dirname(__DIR__));
chdir(ROOT);

$container = Container::instance();
$kernel = $container->get(HttpKernel::class);
assert($kernel instanceof HttpKernel);
$response = $container->call([$kernel, 'handle'], ['request' => $request = Request::fromGlobal()]);
$response->send();
$kernel->terminateRequest($request, $response);
$kernel->terminate();
