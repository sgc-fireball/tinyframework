<?php

declare(strict_types=1);

namespace TinyFramework\Core;

use Closure;

class Pipeline implements PipelineInterface
{
    private array $layers = [];

    public function __construct(Closure|array|null $layers = null)
    {
        if ($layers !== null) {
            $this->layers($layers);
        }
    }

    public function layers(Closure|array $layers): static
    {
        if ($layers instanceof Closure) {
            $layers = [$layers];
        }
        foreach ($layers as $layer) {
            if ($layer instanceof Closure) {
                $this->layers[] = $layer;
            }
        }
        return $this;
    }

    /**
     * @param Closure $core
     * @param mixed|null $parameter
     * @return mixed
     */
    public function call(Closure $core, $parameter = null): mixed
    {
        $chain = array_reduce(
            array_reverse($this->layers),
            function ($next, $layer) {
                return function ($parameter) use ($layer, $next) {
                    return $layer($parameter, $next);
                };
            },
            function ($parameter) use ($core) {
                return $core($parameter);
            }
        );
        return $chain($parameter);
    }
}
