<?php

namespace TinyFramework\Broadcast;

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
        if (!in_array($request->ip(), $whitelist)) {
            return Response::new(null, 404);
        }
        $user = $request->user();
        $channel = validator()->validate($request, ['channel' => 'required|string|min:1'])['channel'];
        if ($this->broadcastManager->auth($channel, $user)) {
            return Response::new(null, 200);
        }
        return Response::new(null, 403);
    }

}
