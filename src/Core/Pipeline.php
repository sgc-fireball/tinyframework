<?php declare(strict_types=1);

namespace TinyFramework\Core;

use Closure;

class Pipeline implements PipelineInterface
{

    private array $layers = [];

    /**
     * @param Closure|Closure[]|null $layers
     */
    public function __construct($layers = null)
    {
        if (is_array($layers) || $layers instanceof Closure) {
            $this->layers($layers);
        }
    }

    /**
     * @param Closure|Closure[] $layers
     * @return PipelineInterface
     */
    public function layers($layers): PipelineInterface
    {
        if ($layers instanceof Closure) {
            $layers = [$layers];
        }
        if (is_array($layers)) {
            foreach ($layers as $layer) {
                if ($layer instanceof Closure) {
                    $this->layers[] = $layer;
                }
            }
        }
        return $this;
    }

    /**
     * @param Closure $core
     * @param mixed|null $parameter
     * @return mixed
     */
    public function call(Closure $core, $parameter = null)
    {
        $chain = $arr = array_reduce(
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
