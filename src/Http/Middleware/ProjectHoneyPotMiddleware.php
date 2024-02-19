<?php

declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use Closure;
use TinyFramework\Core\Config;
use TinyFramework\Http\RequestInterface;
use TinyFramework\Http\Response;

class ProjectHoneyPotMiddleware implements MiddlewareInterface
{
    private ?string $key;

    public function __construct(Config $config)
    {
        $this->key = $config->get('services.projectHoneyPot');
    }

    public function handle(RequestInterface $request, Closure $next, mixed ...$parameters): Response
    {
        if ($result = $this->resolve($request->realIp())) {
            if ($result['type'] > 0) {
                return Response::view('errors.projectHoneyPot', $result, 403);
            }
        }
        return $next($request);
    }

    private function resolve(string|null $ip): ?array
    {
        if ($this->key === null) {
            return null;
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP | FILTER_FLAG_IPV4)) {
            return null;
        }
        $host = sprintf(
            '%s.%s.dnsbl.httpbl.org',
            $this->key,
            implode('.', array_reverse(explode('.', $ip)))
        );
        $ip = gethostbyname($host);
        if ($host === $ip) {
            return null;
        }
        [$prefix, $activity, $threat, $type] = array_map(fn ($item) => (int)$item, explode('.', $ip));
        if ($prefix !== 127) {
            return null;
        }
        return [
            'ip' => $ip,
            'activity' => $activity,
            'threat' => $threat,
            'type' => $type,
        ];
    }
}
