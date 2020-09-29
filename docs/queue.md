# Queue

- [Introduction](#introduction)

## Introduction
```php
queue()->count();
queue()->push((new JobAwesome($yourData))->attempts(3)->delay(60)->queue('priority'));
queue()->name('priority')->pop(); // return null or and a JobInterface
```