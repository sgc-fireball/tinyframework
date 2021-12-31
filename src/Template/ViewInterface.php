<?php

declare(strict_types=1);

namespace TinyFramework\Template;

use Closure;

interface ViewInterface
{
    public function render(string $view, array $data = [], array $parentData = []): string;

    public function exists(string $view): bool;

    public function renderString(string $content, array $data = [], array $parentData = []): string;

    public function clear(): ViewInterface;
}
