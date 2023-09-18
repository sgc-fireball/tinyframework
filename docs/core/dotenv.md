# DotEnv

- [Introduction](#introduction)

## Introduction

## Usge
```php
use TinyFramework\Core\DotEnv;
DotEnv::instance()->load('.env')->load('.env.local');
if (SWOOLE) {
    DotEnv::instance()->load('.env.swoole')
}
```

## .env File
Examples
```dotenv
KEY="value"
KEY='value'
KEY=value
KEY=null
KEY=true
KEY=false
KEY=
```
