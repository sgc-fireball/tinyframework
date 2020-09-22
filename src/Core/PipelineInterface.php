<?php declare(strict_types=1);

namespace TinyFramework\Core;

use Closure;

interface PipelineInterface
{

    /**
     * @param Closure|Closure[]|null $layers
     */
    public function __construct($layers = null);

    /**
     * @param Closure|Closure[] $layers
     * @return PipelineInterface
     */
    public function layers($layers): PipelineInterface;

    /**
     * @param Closure $core
     * @param mixed|null $parameter
     * @return mixed
     */
    public function call(Closure $core, $parameter = null);

}
