<?php

declare(strict_types=1);

namespace TinyFramework\Database\Relations;

abstract class Relation
{
    abstract public function load(): mixed;
}
