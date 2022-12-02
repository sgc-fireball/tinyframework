<?php

declare(strict_types=1);

namespace TinyFramework\Http;

use Closure;
use TinyFramework\Core\KernelInterface;

interface HttpKernelInterface extends KernelInterface
{
    public function handle(Request $request): Response;

    public function terminateRequestCallback(Closure $closure): HttpKernelInterface;

    public function terminateRequest(Request $request, Response $response): HttpKernelInterface;

    public function terminateCallback(Closure $closure): HttpKernelInterface;

    public function terminate(): HttpKernelInterface;
}
