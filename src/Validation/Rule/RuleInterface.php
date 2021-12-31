<?php

declare(strict_types=1);

namespace TinyFramework\Validation\Rule;

interface RuleInterface
{
    public function getName(): string;

    /**
     * @param array $attributes
     * @param string $name
     * @param mixed ...$parameters
     * @return array|bool|null
     *
     * array = error list
     * bool(true) = break checks
     * bool(false)|null = next check
     */
    public function validate(array $attributes, string $name, ...$parameters): array|bool|null;
}
