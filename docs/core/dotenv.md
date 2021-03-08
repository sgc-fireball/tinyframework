# DotEnv

- [Introduction](#introduction)

## Introduction

## Usge
```php
use TinyFramework\Core\DotEnv;
DotEnv::instance()->load('.env')->load('.env.local');
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
