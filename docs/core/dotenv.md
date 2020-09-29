# DotEnv

- [Introduction](#introduction)

## Introduction

## Defaults
- APP_ENV = `production`
- APP_DEBUG = `false`
- APP_URL = `http://localhost`
- APP_SECRET = `null`

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
