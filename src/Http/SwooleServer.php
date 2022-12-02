<?php

namespace TinyFramework\Http;

use Swoole\Websocket\Server as BaseServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\WebSocket\Frame as SwooleWebsocketFrame;
use TinyFramework\Console\Output\Output;
use TinyFramework\Core\ContainerInterface;
use TinyFramework\Event\EventDispatcherInterface;
use TinyFramework\Queue\JobInterface;
use TinyFramework\Queue\SwooleQueue;
use TinyFramework\System\SignalEvent;
use TinyFramework\System\SignalHandler;

class SwooleServer
{

    private ContainerInterface $container;
    private HttpKernelInterface $kernel;
    private EventDispatcherInterface $eventDispatcher;
    private Output $output;
    private BaseServer $server;
    private SwooleQueue $queue;

    /** @var Request[] */
    private array $websockets = [];

    public function __construct(
        ContainerInterface $container,
        HttpKernelInterface $kernel,
        EventDispatcherInterface $eventDispatcher,
        BaseServer $server,
        SwooleQueue $swooleQueue
    ) {
        $this->container = $container;
        $this->kernel = $kernel;
        $this->eventDispatcher = $eventDispatcher;
        $this->server = $server;
        $this->queue = $swooleQueue;

        $this->output = new Output();
    }

    public function handle(): void
    {
        SignalHandler::init($this->container->get(EventDispatcherInterface::class));
        SignalHandler::catchSignal(SignalHandler::SIGWINCH);
        SignalHandler::catchSignal(SignalHandler::SIGTERM);

        $this->eventDispatcher->addListener(SignalEvent::class, function (SignalEvent $event) {
            $this->onSignal($event);
        });

        $this->server->on("Start", function (BaseServer $server): void {
            $this->onStart($server);
        });
        $this->server->on('WorkerStart', function (BaseServer $server, int $workerId): void {
            $this->onWorkerStart($server, $workerId);
        });
        $this->server->on('Task', function (BaseServer $server, int $taskId, int $workerId, mixed $data): void {
            $this->onTask($server, $taskId, $workerId, $data);
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
        $this->server->on('Close', function (BaseServer $server, int $fd): void {
            $this->onConnectionClose($server, $fd);
        });
        $this->server->on("Shutdown", function (BaseServer $server): void {
            $this->onShutdown($server);
        });
        if ($this->server->start()) {
            exit(1);
        }
        exit(0);
    }

    private function onStart(BaseServer $server)
    {
        $this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> Server started',
                date('Y-m-d H:i:s'),
            )
        );
        /*$server->tick(BaseServer::PING_DELAY_MS, function () use ($server) {
            foreach ($server->connections as $id) {
                $server->push($id, 'ping', WEBSOCKET_OPCODE_PING);
            }
        });*/
    }

    private function onShutdown(BaseServer $server)
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
        $response = $this->container->call([$this->kernel, 'handle'], ['request' => $request]);
        assert($response instanceof Response);
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
        $res->end($response->content());
        $end = hrtime(true);
        $time = ($end - $start) / 1E6;
        $code = $response->code();
        $code = is_int($code) && $code < 100 ? '<red>' . $code . '</red>' : $code; // <100
        $code = is_int($code) && $code < 200 ? '<lightblue>' . $code . '</lightblue>' : $code; // 100 < n < 200
        $code = is_int($code) && $code < 300 ? '<green>' . $code . '</green>' : $code; // 200 < n < 300
        $code = is_int($code) && $code < 400 ? '<blue>' . $code . '</blue>' : $code; // 300 < n < 400
        $code = is_int($code) && $code < 500 ? '<orange>' . $code . '</orange>' : $code; // 400 < n < 500
        $code = is_int($code) && $code > 500 ? '<red>' . $code . '</red>' : $code; // >= 500
        $this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> HTTP: ip:%s time:%.2f method:%s url:%s code:%s',
                date('Y-m-d H:i:s'),
                $request->ip(),
                $time,
                $request->method(),
                $request->url()->__toString(),
                $code
            )
        );
        $this->kernel->terminateRequest($request, $response);
    }

    private function onWebsocketOpen(BaseServer $server, SwooleRequest $req): void
    {
        $req->header['request_method'] = 'CONNECT';
        $req->header['scheme'] = 'ws';

        $request = Request::fromSwoole($req, $server);
        $route = $this->container->get('router')->resolve($request);
        if (!$route) {
            $server->close($req->fd);
            $this->output->writeln(
                sprintf(
                    '<yellow>[%s]</yellow> Websocket[%d] Reject connection from %s on %s',
                    date('Y-m-d H:i:s'),
                    $req->fd,
                    $request->ip(),
                    $request->url()->__toString()
                )
            );
            return;
        }

        $this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> Websocket[%d] Connection from %s on %s',
                date('Y-m-d H:i:s'),
                $req->fd,
                $request->ip(),
                $request->url()->__toString()
            )
        );
        $this->websockets[$req->fd] = $request;
    }

    private function onWebsocketMessage(BaseServer $server, SwooleWebsocketFrame $frame): void
    {
        $request = $this->websockets[$frame->fd]->method('POST')->body($frame->data);
        $this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> Websocket[%d] ip:%s url:%s size:%d',
                date('Y-m-d H:i:s'),
                $frame->fd,
                $request->ip(),
                $request->url()->__toString(),
                strlen($frame->data)
            )
        );
        $this->container->call([$this->kernel, 'handle'], ['request' => $request]);
    }

    private function onConnectionClose(BaseServer $server, int $fd): void
    {
        if (array_key_exists($fd, $this->websockets)) {
            unset($this->websockets[$fd]);
        }
        /*$this->output->writeln(
            sprintf(
                '<yellow>[%s]</yellow> Websocket[%d] Close connection.',
                date('Y-m-d H:i:s'),
                $fd
            )
        );*/
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

    private function onTask(BaseServer $server, int $taskId, int $workerId, mixed $data): void
    {
        if ($data instanceof JobInterface) {
            try {
                $data->handle();
                $this->output->writeln(
                    sprintf(
                        '<yellow>[%s]</yellow> Queue[%d] Task[%d] %s: <green>successful</green>.',
                        date('Y-m-d H:i:s'),
                        $workerId,
                        $taskId,
                        get_class($data)
                    )
                );
                $this->queue->ack($data);
            } catch (\Throwable $e) {
                $this->output->writeln(
                    sprintf(
                        '<yellow>[%s]</yellow> Queue[%d] Task[%d] %s: <red>failed</red>.',
                        date('Y-m-d H:i:s'),
                        $workerId,
                        $taskId,
                        get_class($data)
                    )
                );
                $this->queue->nack($data);
            }
        } else {
            $name = is_object($data) ? get_class($data) : (string)$data;
            $this->output->writeln(
                sprintf(
                    '<yellow>[%s]</yellow> <orange>Queue[%d] Task[%d] Unknown: %s</orange>',
                    date('Y-m-d H:i:s'),
                    $workerId,
                    $taskId,
                    $name
                )
            );
        }
    }

    private function onStop(): void
    {
        $this->server->stop();
    }

    private function onSignal(SignalEvent $event): void
    {
        match ($event->signal()) {
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

}
