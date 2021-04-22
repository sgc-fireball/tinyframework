# Event

- [Introduction](#introduction)

## Introduction

### Example

```php
use TinyFramework\System\SignalEvent;

event()->addListener(SignalEvent::class, function(SignalEvent $event) {
    echo 'Received a signal: '.$event->signal();
});

event()->dispatch(new SignalEvent(0, 'SIGNULL'));
```
