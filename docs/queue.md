# Queue

- [Introduction](#introduction)
- [Queue Worker](#queue-worker)

## Introduction

```php
queue()->count();
queue()->push((new JobAwesome($yourData))->attempts(3)->delay(60)->queue('high'));
queue()->name('priority')->pop(); // return null or and a JobInterface
```

## Queue Worker

Ask every queue for a job, if one of these response a job. Work on it and start again from the first queue.

```bash
php console tinyframework:queue:worker --queue high --queue medium --queue low -vvv
```

You can test it with:

```bash
php console tinyframework:shell
```

```php
container('queue')->push((new TinyFramework\Queue\TestJob())->queue('low'));
container('queue')->push((new TinyFramework\Queue\TestJob())->queue('low'));
container('queue')->push((new TinyFramework\Queue\TestJob())->queue('medium'));
container('queue')->push((new TinyFramework\Queue\TestJob())->queue('high'));
container('queue')->push((new TinyFramework\Queue\TestJob())->queue('low'));
```
