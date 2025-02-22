<?php

namespace TinyFramework\OpenAPI\Objects;

use TinyFramework\OpenAPI\OpenAPIException;

/**
 * @link https://swagger.io/specification/#server-variable-object
 */
class ServerVariable extends AbstractObject
{

    /** @var string[] */
    public array $enum = [];
    public string $default;
    public ?string $description = null;

    public static function parse(array $arr): ServerVariable
    {
        $object = new ServerVariable();
        $object->default = $arr['default'];
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        if (array_key_exists('enum', $arr)) {
            $object->enum = array_map(fn(string $enum) => (string)$enum, $arr['enum']);
        }
        $object = $object->parseExtension($arr);
        assert($object instanceof self);
        return $object;
    }

}
