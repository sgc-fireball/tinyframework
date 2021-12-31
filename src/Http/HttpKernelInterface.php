<?php

declare(strict_types=1);

namespace TinyFramework\Http;

use Closure;
use TinyFramework\Core\KernelInterface;

interface HttpKernelInterface extends KernelInterface
{
    public function handle(Request $request): Response;

    public function terminateCallback(Closure $closure): HttpKernelInterface;
}
