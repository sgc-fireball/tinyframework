# TinyFramework

## Container
```php
$service = container();
$service = container($service);
$service = container('config');
$service = container(ConfigInterface::class);
```

## Config
```php
config()->get($key);
config()->set($key, $value);
```

## Cache
```php
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
queue()->push((new JobAwesome(['your-data']))->attempts(3)->delay(60)->queue('priority'));
queue()->name('priority')->pop(); // return null or and a JobInterface
```

## Template Engine
```php
view(); // return ViewInterface
view('path.to.file'); // return Response|ViewInterface
echo view('path.to.file', ['key' => $value]);
```

## Console / PsySh
```bash
php7.4 console
```
