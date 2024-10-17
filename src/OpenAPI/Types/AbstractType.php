<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Objects\Schema;

abstract class AbstractType extends Schema
{

    abstract public function validate(mixed $value): void;

}
