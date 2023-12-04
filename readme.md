# [TinyFramework](https://github.com/sgc-fireball/tinyframework)

- [Introduction](#introduction)
- [How to start](#how-to-start)
- [Debugging](#debugging)
- [Performance](#performance)
- [Author](#author)

- [Documentation](./docs/index.md)
- [Security](./docs/index.md)

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

## Debugging

Open PHPStorm Settings `PHP` / `Servers`:

- Name: `tinyframework`
- Host: `127.0.0.1`
- Port: `9000`
- Debugger: `xdebug`
- Use Path Mapping `Yes`
- Map it to `/app`

Use our preconfigurated alias `phpx`.

```bash
phpx console
```

## Performance

1. Enable composer classmap authoritative.
    ```bash
    composer dump-autoload --optimize-autoloader --classmap-authoritative
    ```

2. Uninstall!!!! xdebug

3. Use PHP OpCache. But be case, and disable caching files under /storage/. Watch
   here [TinyFramework Opcache](https://github.com/sgc-fireball/tinyframework-opcache)

## Todos

- Implement DateTime wrapper
- Implement Input::choise, Input::question, Input::confirm
- Implement Model Relations
- Implement Casts into Models
- Implement Auth Service / Interface
- create tinyframework-echo as a nodejs repo with a node server
- URL Signer
- https://github.com/opis/closure/
- Implement ServiceProviders::provides to implement lazy loading services
- Paginator with url link support

## Ideas

- foreach $loop and $loop->parent
- Markdown interpreter for Str.

## Author

Richard Hülsberg <rh+github@hrdns.de>
