<?php

namespace TinyFramework\OpenAPI\Objects;

use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Types\AbstractType;

/**
 * @link https://swagger.io/specification/#reference-object
 */
class Reference extends AbstractObject
{

    public string $ref;
    public ?string $summary = null;
    public ?string $description = null;
    /**
     * @internal
     */
    public ?AbstractObject $_referenceTarget = null;

    public static function parse(array $arr): Reference
    {
        $object = new Reference();
        if (!array_key_exists('$ref', $arr) || !$arr['$ref']) {
            throw new \InvalidArgumentException('Reference::$ref is missing.');
        }
        if (!str_starts_with($arr['$ref'], '#/')) {
            throw new \InvalidArgumentException('Reference::$ref is missing.');
        }
        $object->ref = $arr['$ref'];
        if (array_key_exists('summary', $arr)) {
            $object->summary = $arr['summary'];
        }
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        $object = $object->parseExtension($arr);
        assert($object instanceof self);
        return $object;
    }

    public function setReference(AbstractObject $object): self
    {
        $this->_referenceTarget = $object;
        return $this;
    }

    public function getReference(): AbstractObject
    {
        return $this->_referenceTarget;
    }

    public function validate(mixed $value): void
    {
        if (!($this->_referenceTarget instanceof AbstractObject)) {
            throw new OpenAPIException('Could not resolve reference.', 503);
        }
        if (!($this->_referenceTarget instanceof AbstractType)) {
            throw new OpenAPIException('Reference is not an instance of AbstractType.', 503);
        }
        $this->_referenceTarget->validate($value);
    }

    public function __get(string $name): mixed
    {
        return $this->_referenceTarget->{$name};
    }

    public function __set(string $name, mixed $value): void
    {
        $this->_referenceTarget->{$name} = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->_referenceTarget->{$name});
    }

    public function __unset(string $name): void
    {
        unset($this->_referenceTarget->{$name});
    }

}
