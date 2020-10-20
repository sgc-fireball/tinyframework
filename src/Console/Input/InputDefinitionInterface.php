<?php declare(strict_types=1);

namespace TinyFramework\Console\Input;

interface InputDefinitionInterface
{

    public static function create(
        string $name,
        string $description = null,
        array $options = [],
        array $arguments = []
    ): InputDefinitionInterface;

    public function name(string $name = null);

    /**
     * @param string|null $description
     * @return $this|string|null
     */
    public function description(string $description = null);

    /**
     * @param Argument|string|int|null $option
     * @return Argument|$this|array|null
     */
    public function argument($argument = null);

    /**
     * @param Option|string|null $option
     * @return Option|$this|array|null
     */
    public function option($option = null);

}
