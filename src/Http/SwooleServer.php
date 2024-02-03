<?php

namespace TinyFramework\Http;

use Swoole\Atomic;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Server\Task;
use Swoole\Timer;
use Swoole\WebSocket\Frame as SwooleWebsocketFrame;
use Swoole\Websocket\Server as BaseServer;
use TinyFramework\Broadcast\BroadcastChannelTable;
use TinyFramework\Cache\SwooleTableCache;
use TinyFramework\Console\Output\Output;
use TinyFramework\Core\ConfigInterface;
use TinyFramework\Core\ContainerInterface;
use TinyFramework\Cron\CronExpression;
use TinyFramework\Cron\CronjobInterface;
use TinyFramework\Event\EventDispatcherInterface;
use TinyFramework\Queue\JobInterface;
use TinyFramework\Queue\SwooleQueue;
use TinyFramework\System\SignalEvent;
use TinyFramework\System\SignalHandler;

use function Swoole\Coroutine\run;

class SwooleServer
{
    private ContainerInterface $container;
    private HttpKernelInterface $kernel;
    private EventDispatcherInterface $eventDispatcher;
    private Output $output;
    private BaseServer $server;
    private SwooleQueue $queue;
    private Atomic $crontick;
    private array $config;

    private SwooleTableCache $cacheTable;
    private WebsocketTable $websocketTable;
    private BroadcastChannelTable $broadcastChannelTable;

    public function __construct(
        ContainerInterface $container,
        HttpKernelInterface $kernel,
        EventDispatcherInterface $eventDispatcher,
        BaseServer $server,
        SwooleQueue $swooleQueue,
        ConfigInterface $config
    ) {
        $this->container = $container;
        $this->kernel = $kernel;
        $this->eventDispatcher = $eventDispatcher;
        $this->server = $server;
        $this->queue = $swooleQueue;
        $this->config = $config->get('swoole');
        $this->output = new Output();
        $this->crontick = new Atomic((int)date('Hi'));

        $this->cacheTable = new SwooleTableCache();
        $this->websocketTable = new WebsocketTable();
        $this->broadcastChannelTable = new BroadcastChannelTable();
        $this->container->singleton(self::class, $this);
        $this->container->singleton(BaseServer::class, $server);
        $this->container->singleton(SwooleTableCache::class, $this->cacheTable);
        $this->container->singleton(WebsocketTable::class, $this->websocketTable);
        $this->container->singleton(BroadcastChannelTable::class, $this->broadcastChannelTable);
    }

    public function handle(): void
    {
        SignalHandler::init($this->container->get(EventDispatcherInterface::class));
        SignalHandler::catchSignal(SignalHandler::SIGQUIT);
        SignalHandler::catchSignal(SignalHandler::SIGUSR1);
        SignalHandler::catchSignal(SignalHandler::SIGUSR2);
        SignalHandler::catchSignal(SignalHandler::SIGTERM);
        SignalHandler::catchSignal(SignalHandler::SIGWINCH);

        $this->eventDispatcher->addListener(SignalEvent::class, function (SignalEvent $event) {
            $this->output->writeln(
                sprintf(
                    '<yellow>[%s]</yellow> Received signal: %s',
                    date('Y-m-d H:i:s'),
                    $event->name()
                )
            );
            $this->onSignal($event);
        });

        $this->server->on("Start", function (BaseServer $server): void {
            $this->onStart($server);
        });
        $this->server->on('WorkerStart', function (BaseServer $server, int $workerId): void {
            $this->onWorkerStart($server, $workerId);
        });
        $this->server->on('Task', function (BaseServer $server, Task $task): void {
            $this->onTask($server, $task);
        });
        $this->server->on('WorkerStop', function (BaseServer $server, int $workerId): void {
            $this->onWorkerStop($server, $workerId);
        });
        $this->server->on('Request', function (SwooleRequest $req, SwooleResponse $res): void {
            $this->onHttpRequest($req, $res);
        });
        $this->server->on('Open', function (BaseServer $server, SwooleRequest $req): void {
            $this->onWebsocketOpen($server, $req);
        });
        $this->server->on('Message', function (BaseServer $server, SwooleWebsocketFrame $frame): void {
            $this->onWebsocketMessage($server, $frame);
        });
        $this->server->on('Disconnect', function (BaseServer $server, int $fd): void {
            $this->onConnectionDisconnect($server, $fd);
        });
        $this->server->on("Shutdown", function (BaseServer $server): void {
            $this->onShutdown($server);
        });
        if (!in_array($this->config['settings']['dispatch_mode'], [1, 3, 7])) {
            $this->server->on('Close', function (BaseServer $server, int $fd): void {
                $this->onConnectionClose($server, $fd);
            });
        }
        Timer::tick(5 * 1000, function () { // tick every 5 seconds
            $now = (int)date('Hi');
            if ($this->crontick->get() !== $now) {
                $this->crontick->set($now);
                $this->server->task('cronjobs'); // run this job only once in a minute
            }
        });
        if ($this->server->start()) {
            exit(1);
        }
        exit(0);
    }

    private function onStart(BaseServer $server): void
    {
        $this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> Server started',
                date('Y-m-d H:i:s'),
            )
        );
    }

    private function onShutdown(BaseServer $server): void
    {
        $this->kernel->terminate();
        $this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> Server shutdown',
                date('Y-m-d H:i:s')
            )
        );
    }

    private function onHttpRequest(SwooleRequest $req, SwooleResponse $res): void
    {
        $start = hrtime(true);
        $request = Request::fromSwoole($req, $this->server);
        try {
            $response = $this->container->call([$this->kernel, 'handle'], ['request' => $request]);
            assert($response instanceof Response);
        } catch (\Throwable $e) {
            $response = $this->kernel->throwableToResponse($e);
        }
        foreach ($response->headers() as $key => $value) {
            if (in_array($key, Response::$multiLineHeader) && is_array($value)) {
                foreach ($value as $value2) {
                    $res->header($key, $value2);
                }
            } else {
                $res->header($key, $value);
            }
        }
        $res->status($response->code());
        $size = strlen($response->content());
        $res->end($response->content());
        $end = hrtime(true);
        $time = ($end - $start) / 1E6;
        $code = $response->code();
        $code = is_int($code) && $code < 100 ? '<white>' . $code . '</white>' : $code; // <100
        $code = is_int($code) && $code < 200 ? '<lightblue>' . $code . '</lightblue>' : $code; // 100 < n < 200
        $code = is_int($code) && $code < 300 ? '<blue>' . $code . '</blue>' : $code; // 200 < n < 300
        $code = is_int($code) && $code < 400 ? '<gray>' . $code . '</gray>' : $code; // 300 < n < 400
        $code = is_int($code) && $code < 500 ? '<orange>' . $code . '</orange>' : $code; // 400 < n < 500
        $code = is_int($code) && $code > 500 ? '<red>' . $code . '</red>' : $code; // >= 500
        $this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> HTTP: ip:%s method:%s url:%s time:%.2f size:%d code:%s',
                date('Y-m-d H:i:s'),
                $request->realIp(),
                $request->method(),
                $request->url()->__toString(),
                $time,
                $size,
                $code
            )
        );
        $this->kernel->terminateRequest($request, $response);
    }

    private function onWebsocketOpen(BaseServer $server, SwooleRequest $req): void
    {
        $req->server['request_method'] = 'WEBSOCKET';
        $req->header['scheme'] = preg_replace('/^http/', 'ws', $req->header['scheme']);
        $request = Request::fromSwoole($req, $server);
        /** @var Route|null $route */
        $route = $this->container->get('router')->resolve($request);
        if (!$route) {
            $server->close($req->fd);
            $this->output->writeln(
                sprintf(
                    '<yellow>[%s]</yellow> Websocket[%d] Reject connection from %s on %s',
                    date('Y-m-d H:i:s'),
                    $req->fd,
                    $request->realIp(),
                    $request->url()->__toString()
                )
            );
            return;
        }

        $this->broadcastChannelTable->allow($req->fd, 'system');
        $this->broadcastChannelTable->allow($req->fd, $request->url()->__toString());
        logger()->info('CHANNEL join: '.$request->url()->__toString());

        $this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> Websocket[%d] Connection from %s on %s',
                date('Y-m-d H:i:s'),
                $req->fd,
                $request->realIp(),
                $request->url()->__toString()
            )
        );
        $this->websocketTable->set(
            (string)$req->fd,
            [
                'id' => $req->fd,
                'request' => serialize($request),
            ]
        );
    }

    private function onWebsocketMessage(BaseServer $server, SwooleWebsocketFrame $frame): void
    {
        $start = hrtime(true);
        $body = $this->websocketTable->get((string)$frame->fd, 'request');
        if (!$body) {
            return;
        }
        $request = unserialize($body);
        assert($request instanceof Request);
        $request = $request->method('WEBSOCKET')->body($frame->data);
        /** @var Response $response */
        $response = $this->container->call([$this->kernel, 'handle'], ['request' => $request]);

        $code = $response->code();
        $code = is_int($code) && $code < 100 ? '<red>' . $code . '</red>' : $code; // <100
        $code = is_int($code) && $code < 200 ? '<lightblue>' . $code . '</lightblue>' : $code; // 100 < n < 200
        $code = is_int($code) && $code < 300 ? '<green>' . $code . '</green>' : $code; // 200 < n < 300
        $code = is_int($code) && $code < 400 ? '<blue>' . $code . '</blue>' : $code; // 300 < n < 400
        $code = is_int($code) && $code < 500 ? '<orange>' . $code . '</orange>' : $code; // 400 < n < 500
        $code = is_int($code) && $code > 500 ? '<red>' . $code . '</red>' : $code; // >= 500

        $end = hrtime(true);
        $time = ($end - $start) / 1E6;
        $this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> Websocket[%d] ip:%s url:%s time:%.2f size:%d code:%s',
                date('Y-m-d H:i:s'),
                $frame->fd,
                $request->realIp(),
                $request->url()->__toString(),
                $time,
                strlen($frame->data),
                $code
            )
        );
    }

    private function onConnectionClose(BaseServer $server, int $fd): void
    {
        $key = (string)$fd;
        if ($this->websocketTable->exists($key)) {
            $this->websocketTable->delete($key);
            $server->close($key);
        }
        $this->broadcastChannelTable->cleanup($fd);
    }

    private function onConnectionDisconnect(BaseServer $server, int $fd): void
    {
        $key = (string)$fd;
        if ($this->websocketTable->exists($key)) {
            $this->websocketTable->delete($key);
            $server->close($key);
        }
        $this->broadcastChannelTable->cleanup($fd);
    }

    private function onWorkerStart(BaseServer $server, int $workerId): void
    {
        $this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> Worker[%d] started',
                date('Y-m-d H:i:s'),
                $workerId
            )
        );
    }

    private function onWorkerStop(BaseServer $server, int $workerId): void
    {
        $this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> Worker[%d] stopped',
                date('Y-m-d H:i:s'),
                $workerId
            )
        );
    }

    private function onTask(BaseServer $server, Task $task): void
    {
        if ($task->data === 'cronjobs') {
            $this->handleCronjobs();
        } elseif ($task->data instanceof JobInterface) {
            $error = null;
            $start = hrtime(true);
            try {
                $task->data->handle();
                $this->queue->ack($task->data);
            } catch (\Throwable $e) {
                $error = exception2text($e);
                $this->queue->nack($task->data);
            } finally {
                $end = hrtime(true);
                $time = ($end - $start) / 1E6;
                $this->output->writeln(
                    sprintf(
                        '<yellow>[%s]</yellow> Queue[%d] task:%d class:%s time:%.2f state:%state',
                        date('Y-m-d H:i:s'),
                        $task->worker_id,
                        $task->id,
                        get_class($task->data),
                        $time,
                        $error ? '<red>failed</red>' : '<green>successful</green>'
                    )
                );
                if ($error) {
                    $this->output->error($error);
                }
            }
        } else {
            $name = is_object($task->data) ? get_class($task->data) : (string)$task->data;
            $this->output->writeln(
                sprintf(
                    '<yellow>[%s]</yellow> <orange>Queue[%d] Task[%d] unknown:%s</orange>',
                    date('Y-m-d H:i:s'),
                    $task->worker_id,
                    $task->id,
                    $name
                )
            );
        }
    }

    private function onStop(): void
    {
        $this->server->stop();
    }

    private function onReload(): void
    {
        $this->server->reload();
    }

    private function onSignal(SignalEvent $event): void
    {
        match ($event->signal()) {
            SignalHandler::SIGQUIT => $this->onStop(),
            SignalHandler::SIGUSR1 => $this->onReload(),
            SignalHandler::SIGUSR2 => $this->onReload(),
            SignalHandler::SIGTERM => $this->onStop(),
            SignalHandler::SIGWINCH => $this->output->onResize(),
            default => $this->output->writeln(
                sprintf(
                    '<yellow>[%s]</yellow> <orange>Received unhandled signal: %s</orange>',
                    date('Y-m-d H:i:s'),
                    $event->signal()
                )
            )
        };
    }

    private function handleCronjobs(): void
    {
        $timezone = new \DateTimeZone($this->container->get('config')->get('app.timezone', 'UTC'));
        $now = new \DateTimeImmutable('now', $timezone);

        /** @var CronjobInterface[] $jobs */
        $jobs = array_filter(
            $this->container->tagged('cronjob'),
            function (CronjobInterface $job) use ($now) {
                $cron = new CronExpression($job->expression());
                return $cron->isDue($now);
            }
        );

        $this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> Cronjob Found %d jobs to execute',
                date('Y-m-d H:i:s'),
                count($jobs)
            )
        );

        if (count($jobs) === 0) {
            return;
        }

        return;

        run(function () use ($jobs) {
            foreach ($jobs as $job) {
                go(function () use ($job) {
                    $start = hrtime(true);
                    $error = null;
                    try {
                        $job->handle();
                        $state = 'successful';
                    } catch (\Throwable $e) {
                        $state = 'failed';
                        $error = exception2text($e, true);
                    } finally {
                        $end = hrtime(true);
                        $time = ($end - $start) / 1E6;
                        $this->output->writeln(
                            sprintf(
                                '<yellow>[%s]</yellow> Cronjob class:%s time:%.2f state:%s',
                                date('Y-m-d H:i:s'),
                                get_class($job),
                                $time,
                                $state
                            )
                        );
                        if ($error) {
                            $this->output->error($error);
                        }
                    }
                });
            }
        });
    }
}
