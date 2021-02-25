# Console

- [Introduction](#introduction)

## Introduction
```bash
php console --help
php console tinyframework:cache:clear
php console tinyframework:down
php console tinyframework:queue:worker
php console tinyframework:shell
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

## Output
### ProgressBar
TBD

### Table
TBD
