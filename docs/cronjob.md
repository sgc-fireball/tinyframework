# Cronjob

- [Introduction](#introduction)

## Introduction
With the Cronjob service you can manage your cronjobs 
in the Applications itself. All you have to do is
execute the `php console tinyframework:cronjob` command once a minute.

### Setup
Tag your cronjobs with `cronjob` and implement the CronjobInterface.

```php
container()->tag('cronjob', [MyCronjob::class]);
```

```php
class MyCronjob implements \TinyFramework\Cron\CronjobInterface
{

    public function expression(): string
    {
        return '@daily';
    }

    public function handle(): void
    {
        // do what ever you want once a day
    }

}
```
