# Cache

- [Introduction](#introduction)

## Introduction
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
