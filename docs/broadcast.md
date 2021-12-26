# Broadcast

- [Introduction](#introduction)
- [Setup](#setup)

## Introduction

The Broadcast system used non-external services. Connection will be established via WebSockets.

## Setup

```bash
npm install @sgc-fireball/tinyframework-echo --save
```

## Build your Server

Create a file `echo.js` with the following content:

```javascript
require('dotenv').config();

const {server, broadcast} = require('@sgc-fireball/tinyframework-echo/src/echo')();

setInterval(() => {
    broadcast('system', {time: (new Date()).getTime()});
}, 5000);

const stopping = () => {
    console.warn("\nSignal received. Stopping ...");
    server.close();
    process.exit(0);
};

process.on('SIGHUP', stopping); // 1 - Temrinal closed
process.on('SIGINT', stopping); // 2 - CRTL-C
process.on('SIGQUIT', stopping); // 3 - Terminal disconnected
process.on('SIGTERM', stopping); // 15 - kill <pid>
```

Start with `node echo.js`

## Build your client

```javascript
const Broadcast = require('@sgc-fireball/tinyframework-echo/src/client');
Broadcast.subscribe('public');
Broadcast.subscribe('private');
Broadcast.subscribe('system');
Broadcast.on((channel, payload) => {
    console.log('Received message in ', channel, 'with payload', payload);
})
```

## Access configuration

Open your route file (for example: `routes/channel.php`).

```php
<?php declare(strict_types=1);

use App\Models\News;
use TinyFramework\Broadcast\BroadcastManager;

/** @var BroadcastManager $manager */
$manager->pattern('news', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}');
$manager->bind('news', fn(string $news) => News::query()->where('id', '=', $news)->first());
$manager->channel('news.{news}', function (string $channel, mixed $user, News $news): bool {
    return true;
});

$manager->channel('public', function (string $channel, mixed $user): bool {
    return true;
});

$manager->channel('private', function (string $channel, mixed $user): bool {
    return false;
});
```

`return true` for access grant and return `false` for access denied. Here you can use the normal route syntax to define
routes and pattern and bindings to auto resolve dependencies.
