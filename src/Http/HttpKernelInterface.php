<?php declare(strict_types=1);

namespace TinyFramework\Http;

use Closure;
use TinyFramework\Core\Kernel;
use TinyFramework\Core\KernelInterface;
use TinyFramework\Core\Pipeline;
use TinyFramework\Exception\HttpException;
use TinyFramework\Template\Blade;

interface HttpKernelInterface extends KernelInterface
{

    public function handle(Request $request): Response;

    public function terminateCallback(Closure $closure): HttpKernelInterface;
    
}
