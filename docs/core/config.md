# Config

- [Introduction](#introduction)

## Introduction
```php
$key = 'app.debug';
config(); // \TinyFramework\Core\Config
config()->get($key); // mixed|null
config()->set($key, $value); // \TinyFramework\Core\Config
```

## Example
File: `config/example.php`
```php
<?php declare(strict_types=1);

return [
    'test' => 1
];
```

Some other File
```php
config('example.test'); // 1
config('example.test', 2); // \TinyFramework\Core\Config
config('example.test'); // 2
```
