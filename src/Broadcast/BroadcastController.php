<?php

namespace TinyFramework\Broadcast;

use Swoole\Websocket\Server as BaseServer;
use TinyFramework\Http\Request;
use TinyFramework\Http\Response;

class BroadcastController
{
    public function __construct(protected BroadcastManager $broadcastManager)
    {
    }

    public function auth(Request $request): Response
    {
        $whitelist = config('broadcast.global.whitelist') ?? [];
        if (!in_array($request->realIp(), $whitelist)) {
            return Response::new(null, 404);
        }
        $user = $request->user();
        $channel = validator()->validate($request, ['channel' => 'required|string|min:1'])['channel'];
        if ($this->broadcastManager->auth($channel, $user)) {
            return Response::new(null);
        }
        return Response::new(null, 403);
    }

    public function websocket(Request $request, BaseServer $server, BroadcastChannelTable $broadcastChannelTable): void
    {
        $message = $request->json() ?: [];
        $message['type'] ??= '';
        match ($message['type']) {
            'ping' => $this->websocketMessagePing($request, $server, $message),
            'subscribe' => $this->websocketMessageSubscribe($request, $server, $message, $broadcastChannelTable),
            'unsubscribe' => $this->websocketMessageUnsubscribe($request, $server, $message, $broadcastChannelTable),
            default => null,
        };
    }

    private function websocketMessagePing(Request $request, BaseServer $server, array $message): void
    {
        $server->push(
            (int)$request->attribute('swoole_fd'),
            json_encode([
                'type' => 'pong',
                'pong' => $message['ping'] ?? time(),
            ])
        );
    }

    private function websocketMessageSubscribe(
        Request $request,
        BaseServer $server,
        array $message,
        BroadcastChannelTable $broadcastChannelTable
    ): void {
        $channel = $message['channel'] ?? null;
        if (!$channel) {
            return;
        }
        $user = $request->user();
        $fd = (int)$request->attribute('swoole_fd');

        if (!$broadcastChannelTable->isAllowed($fd, $channel)) {
            if (!$this->broadcastManager->auth($channel, $user)) {
                return;
            }
            $broadcastChannelTable->allow($fd, $channel);
        }

        $server->push(
            $fd,
            json_encode([
                'type' => 'subscribed',
                'channel' => $channel,
            ])
        );
    }

    private function websocketMessageUnsubscribe(
        Request $request,
        BaseServer $server,
        array $message,
        BroadcastChannelTable $broadcastChannelTable
    ): void {
        $channel = $message['channel'];
        if (!$channel) {
            return;
        }

        $fd = (int)$request->attribute('swoole_fd');
        if ($broadcastChannelTable->isAllowed($fd, $channel)) {
            $broadcastChannelTable->disallow($fd, $channel);
            $server->push(
                $fd,
                json_encode([
                    'type' => 'unsubscribed',
                    'channel' => $channel,
                ])
            );
        }
    }
}
