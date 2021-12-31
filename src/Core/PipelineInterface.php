<?php

declare(strict_types=1);

namespace TinyFramework\Core;

use Closure;

interface PipelineInterface
{
    public function __construct(Closure|array|null $layers = null);

    public function layers(Closure|array $layers): PipelineInterface;

    public function call(Closure $core, mixed $parameter = null): mixed;
}
