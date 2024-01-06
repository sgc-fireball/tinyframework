<?php

declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use Closure;
use TinyFramework\Core\KernelInterface;
use TinyFramework\Exception\HttpException;
use TinyFramework\Http\Request;
use TinyFramework\Http\RequestInterface;
use TinyFramework\Http\Response;

class MaintenanceMiddleware implements MiddlewareInterface
{
    protected KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function handle(RequestInterface $request, Closure $next, mixed ...$parameters): Response
    {
        if ($config = $this->kernel->getMaintenanceConfig()) {
            if (array_key_exists('whitelist', $config)) {
                $whitelist = is_array($config['whitelist']) ? $config['whitelist'] : [$config['whitelist']];
                if (in_array($request->realIp(), $whitelist)) {
                    return $next($request);
                }
            }
            throw new HttpException('Maintenance Mode', 599);
        }
        return $next($request);
    }
}
