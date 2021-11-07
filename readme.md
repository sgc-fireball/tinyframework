# TinyFramework

- [Introduction](#introduction)
- [Documentation](./docs/index.md)
- [Author](#author)

## Introduction

TinyFramework started as a small teaching project and continues to grow into a mature "full-vendor" PHP framework. The
goal of the exercise was to build a framework that resides exclusively in the vendor directory and has no dependencies
to other dependencies.

The implementations of all PSR standards were already removed after the first few classes. The reason for this was that
all subareas would have to be implemented differently and inconsistently.

## How to start

```bash
composer create-project --stability=dev --remove-vcs sgc-fireball/tinyframework-skeleton my-project master
cd my-project; php console
```

## Folders

- app
    - Commands
    - Http
        - Controllers
            - Api
        - Middleware
    - Providers
- config
- database
    - migrations
- public
- resources
    - lang
        - en
    - views
- storage
    - cache
    - logs
    - psych
    - sessions

## Performance

1. Enable composer classmap authoritative.
    ```bash
    composer dump-autoload --optimize-autoloader --classmap-authoritative
    ```

2. Disable xhprof:
    ```dotenv
    XHPROF_ENABLE=false
    ```

3. Uninstall!!!! xdebug

4. Use PHP OpCache But be case, and disable caching files under /storage/. Watch
   here [TinyFramework Opcache](https://github.com/sgc-fireball/tinyframework-opcache)

### Swoole Server

You can use theses server with and/or without an reverse nginx proxy.

```bash
php swoole
```

## Todos

- https://php.watch/articles/php-zend-engine-special-inlined-functions
- Implement Model Relations
- Implement Casts into Models
- Implement Auth Service / Interface
- create tinyframework-echo as a nodejs repo with a ndoe server
- URL Signer
- https://github.com/opis/closure/
- Drop Swoole Support
- Implement ServiceProviders::provides to implement lazy loading services
- extract Output Box as Console/Output/Component

## Author

Richard Hülsberg <rh+github@hrdns.de>
