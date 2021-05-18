#!/usr/bin/env php
<?php declare(strict_types=1);

use Swoole\Http\Server;
use TinyFramework\Core\Container;
use TinyFramework\Http\HttpKernel;
use TinyFramework\Http\Request;
use TinyFramework\Core\DotEnv;
use TinyFramework\Core\DotEnvInterface;

define('ROOT', __DIR__);
chdir(ROOT);
require_once('vendor/autoload.php');
if (file_exists('vendor/composer/platform_check.php')) {
    require_once('vendor/composer/platform_check.php');
}

if (!class_exists(Server::class)) {
    echo "Missing swoole extension.\n";
    echo "sudo apt-get install g++ make php8.0-dev php8.0-curl\n";
    echo "sudo pecl install swoole\n";
    echo "sudo phpenmod curl\n";
    echo "sudo phpenmod swoole\n";
    echo "\n";
    exit(1);
}

$container = Container::instance()->singleton(DotEnvInterface::class, DotEnv::class);

$staticFileTypes = [
    'css' => 'text/css',
    'js' => 'text/javascript',
    'json' => 'application/json',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'jpg' => 'image/jpg',
    'jpeg' => 'image/jpg',
    'mp4' => 'video/mp4',
    'pkpass' => 'application/vnd.apple.pkpass',
    'ico' => 'image/vnd.microsoft.icon',
    'txt' => 'text/plain',
    'xml' => 'text/xml',
];

/**
 * @see https://www.swoole.co.uk/docs/modules/swoole-server/configuration
 * @see https://www.swoole.co.uk/docs/modules/swoole-server-start
 * @var HttpKernel $kernel
 */
$kernel = $container->get(HttpKernel::class);
$server = $container->singleton('swoole', function () use (&$container, &$kernel, &$staticFileTypes) {
    $config = container('config');

    $server = new Server(
        $config->get('swoole.host'),
        (int)$config->get('swoole.port'),
        (int)$config->get('swoole.mode'),
        (int)$config->get('swoole.sock_type')
    );
    $server->set($config->get('swoole.config'));

    $server->on('start', function ($server) {
        printf("HTTP server started at %s:%s\n", $server->host, $server->port);
        printf("Master  PID: %d\n", $server->master_pid);
        printf("Manager PID: %d\n", $server->manager_pid);
    });

    $server->on('request', function (Swoole\Http\Request $req, Swoole\Http\Response $res) use (&$container, &$kernel, &$staticFileTypes) {
        $request = Request::fromSwooleRequest($req);

        $publicPath = rtrim(ROOT, '/') . '/public';
        $staticFile = $publicPath . '/' . ltrim($req->server['request_uri'], '/');
        if (file_exists($staticFile)) {
            $staticFile = realpath($staticFile);
            if (strpos($staticFile, $publicPath) === 0) {
                $type = pathinfo($staticFile, PATHINFO_EXTENSION);
                if (array_key_exists($type, $staticFileTypes)) {
                    printf("200 - %s %s\n", $request->method(), $request->url()->path());
                    $res->header('Content-Type', $staticFileTypes[$type]);
                    $res->sendfile($staticFile);
                    return;
                }
            }
        }

        /** @var \TinyFramework\Http\Response $response */
        $response = $container->call([$kernel, 'handle'], ['request' => $request]);
        printf("%3d - %s %s\n", $response->code(), $request->method(), $request->url()->path());
        $res->status($response->code());
        foreach ($response->headers() as $key => $value) {
            $res->header($key, $value);
        }
        $res->end($response->content());
        $kernel->terminateRequest($request, $response);
    });

    $server->on('shutdown', function () use ($kernel) {
        $kernel->terminate();
    });

    return $server;
})->get('swoole')->start();
