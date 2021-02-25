# TinyFramework

- [Introduction](#introduction)
- [Documentation](./docs/index.md)
- [Author](#author)

## Introduction
TinyFramework started as a small teaching project and continues to grow
into a mature "full-vendor" PHP framework. The goal of the exercise was 
to build a framework that resides exclusively in the vendor directory 
and has no dependencies to other dependencies.

The implementations of all PSR standards were already removed after the
first few classes. The reason for this was that all subareas would have
to be implemented differently and inconsistently.

## Folders
- app
    - Commands
    - Http
        - Controllers
            - Api
        - Middleware
    - Providers
- config
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

## Author
Richard Hülsberg <rh+github@hrdns.de>
