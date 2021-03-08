# Router

- [Introduction](#introduction)
- [config.php](#config.php)
- [Other](#other)

## Introduction
By default, three files are loaded from the "routes" folder in the following order:
- config.php
- api.php
- web.php

You can load additional files via the router service.

## config.php
```php
use TinyFramework\Http\Router;

/** @var Router $router */

$router->pattern('model', '\d+');
$router->bind('model', function($id) {
    return container('database')->query()->table('models')->where('id', '=', $id)->first();
});
```

## Other
```php
use TinyFramework\Http\Router;
use TinyFramework\Http\Request;
use TinyFramework\Http\Response;
use TinyFramework\Http\Middleware\SessionMiddleware;

/** @var Router $router */

$router->any('/', '\DashboardController@index')->name('home.any');
$router->get('/', '\DashboardController@index')->name('home.get');
$router->head('/', '\DashboardController@index')->name('home.head');
$router->post('/', '\DashboardController@index')->name('home.post');
$router->put('/', '\DashboardController@index')->name('home.put');
$router->delete('/', '\DashboardController@index')->name('home.delete');
$router->connect('/', '\DashboardController@index')->name('home.connect');
$router->options('/', '\DashboardController@index')->name('home.opzions');
$router->patch('/', '\DashboardController@index')->name('home.patch');
$router->purge('/', '\DashboardController@index')->name('home.purge');
$router->trace('/', '\DashboardController@index')->name('home.trace');
$router->custom('ABCDEF', '/', '\DashboardController@index')->name('home.custom');

$router->resource('model', '\ModelController', [ // optional overwrites
    'url' => 'model',
    'parameter' => 'model',
    'only' => ['index', 'create', 'store', 'show', 'edit', 'update', 'delete'],
    'names' => [
        'index' => 'model.index',
        'create' => 'model.create',
        'store' => 'model.store',
        'show' => 'model.show',
        'edit' => 'model.edit',
        'update' => 'model.update',
        'delete' => 'model.delete',
    ]
]);

$router->group([
    'scheme' => 'https',
    'domain' => 'example.de',
    'prefix' => '/auth',
    'middleware' => [SessionMiddleware::class]],
    function (Router $router) {
    
    }
);

$router->fallback(function (Request $request) {
    return Response::redirect('/', 302);
});
```
