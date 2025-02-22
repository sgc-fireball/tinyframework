<?php

namespace TinyFramework\OpenAPI\Objects;

use stdClass;

class Callback extends AbstractObject
{

    /** @var null|stdClass<PathItem|Reference> */
    public ?object $paths = null;

    public static function parse(array $arr): Callback
    {
        $object = new Callback();
        foreach ($arr as $key => $pathItem) {
            $object->paths ??= new stdClass();
            if (array_key_exists('$ref', $pathItem)) {
                $object->paths->{$key} = Reference::parse($pathItem);
            } else {
                $object->paths->{$key} = PathItem::parse($pathItem);
            }
        }
        $object = $object->parseExtension($arr);
        assert($object instanceof self);
        return $object;
    }
}
