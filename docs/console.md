# Console

- [Introduction](#introduction)
- [Options](#options)
- [Arguments](#arguments)
- [Bash Completion](#bash-completion)

## Introduction

```bash
php console --help
php console tinyframework:bash:completion
php console tinyframework:cache:clear
php console tinyframework:config:show
php console tinyframework:cronjob
php console tinyframework:database
php console tinyframework:down
php console tinyframework:ide:helper
php console tinyframework:migrate
php console tinyframework:package:build
php console tinyframework:queue:worker
php console tinyframework:queue:worker:stop
php console tinyframework:router:list
php console tinyframework:serve
php console tinyframework:session:clear
php console tinyframework:shell
php console tinyframework:systemd:install
php console tinyframework:up
php console tinyframework:view:clear
```

## Options

Each option must have at least one long parameter. Furthermore,
these can optionally be given a short name.
There are different option types:

- count parameters (example -vvv)
- with parameters (example --env=prod)

Short parameters can also be defined as a set:
`-abce=123` In this case it would be:

- `-a`
- `-b`
- `-c`
- `-e=123`

In this case the equal sign can also be omitted or replaced by a space.

## Arguments

Arguments also exist with console commands and can be either mandatory or optional.

## Bash Completion

```bash
php console tinyframework:bash:completion > /etc/bash_completion.d/tinyframework
reset
/path/to/console<TAB> # now you can use your auto completion
```
