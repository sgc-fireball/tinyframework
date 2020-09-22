<?php declare(strict_types=1);

namespace TinyFramework\Event;

interface EventInterface
{

    public function stopPropagation(): EventInterface;

    public function isPropagationStopped(): bool;

}
