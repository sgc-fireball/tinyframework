# TinyFramework

## Folders
- app
    - Commands
    - Providers
- config
- public
- resources
    - views
- storage
    - cache
    - logs
    - psych
    - sessions 

## Composer
```bash
php7.4 $(which composer) --help
php7.4 $(which composer) tinyframework:cache:clear
php7.4 $(which composer) tinyframework:down
php7.4 $(which composer) tinyframework:queue:worker
php7.4 $(which composer) tinyframework:shell
php7.4 $(which composer) tinyframework:up
php7.4 $(which composer) tinyframework:view:clear
```

## Container
```php
$container = container();
$service = container('config');
$service = container(ConfigInterface::class);
```

## Config
```php
$key = 'app.debug';
config()->get($key);
config()->set($key, $value);
```

## Cache
```php
$key = 'my:cache:key';
cache()->clear();
cache()->get($key);
cache()->set($key, $value);
cache()->set($key, $value, 60 /* seconds */);
cache()->remember($key, function() { return time(); }); // for ever
cache()->remember($key, function() { return time(); }, 60); // for 60 seconds.
cache()->forget($key);

# with tags
cache()->tag([$tag])->clear();
cache()->tag([$tag])->get($key);
cache()->tag([$tag])->set($key, $value);
cache()->tag([$tag])->set($key, $value, 60 /* seconds */);
cache()->tag([$tag])->remember($key, function() { return time(); }); // for ever
cache()->tag([$tag])->remember($key, function() { return time(); }, 60); // for 60 seconds.
cache()->tag([$tag])->forget($key);
```

## Session
```php
session()->get($key);
session()->set($key, $value);
session()->forget($key);
```

## Logger
```php
logger()->alert($message, $context);
logger()->critical($message, $context);
logger()->debug($message, $context);
logger()->emergency($message, $context);
logger()->error($message, $context);
logger()->info($message, $context);
logger()->notice($message, $context);
logger()->warning($message, $context);
logger()->log($level, $message, $context);
```

## Queue
```php
queue()->count();
queue()->push((new JobAwesome($yourData))->attempts(3)->delay(60)->queue('priority'));
queue()->name('priority')->pop(); // return null or and a JobInterface
```

## Template Engine
```php
view(); // return ViewInterface
view('path.to.file'); // return Response
view('path.to.file', ['key' => $value]);
```

## Console / PsySh
```bash
php7.4 console
```
