<?php

declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use Closure;
use TinyFramework\Exception\HttpException;
use TinyFramework\Http\Request;
use TinyFramework\Http\Response;

class MaintenanceMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next, mixed ...$parameters): Response
    {
        if ($config = inMaintenanceMode()) {
            if (array_key_exists('whitelist', $config)) {
                $whitelist = is_array($config['whitelist']) ? $config['whitelist'] : [$config['whitelist']];
                if (in_array($request->ip(), $whitelist)) {
                    return $next($request);
                }
            }
            throw new HttpException('Maintenance Mode', 599);
        }
        return $next($request);
    }
}
