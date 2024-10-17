<?php

namespace TinyFramework\OpenAPI\Objects;

use _PHPStan_a81df6648\Nette\Schema\Elements\AnyOf;
use TinyFramework\OpenAPI\Types\AllOfType;
use TinyFramework\OpenAPI\Types\ArrayType;
use TinyFramework\OpenAPI\Types\BoolType;
use TinyFramework\OpenAPI\Types\IntegerType;
use TinyFramework\OpenAPI\Types\NullType;
use TinyFramework\OpenAPI\Types\NumberType;
use TinyFramework\OpenAPI\Types\ObjectType;
use TinyFramework\OpenAPI\Types\OneOfType;
use TinyFramework\OpenAPI\Types\StringType;

/**
 * @see https://swagger.io/specification/#schema-object
 */
abstract class Schema extends AbstractObject
{

    public static function parse(array $arr): Schema|Reference
    {
        if (array_key_exists('$ref', $arr)) {
            return Reference::parse($arr);
        }
        if (array_key_exists('allOf', $arr)) {
            return AllOfType::parse($arr);
        }
        if (array_key_exists('oneOf', $arr)) {
            return OneOfType::parse($arr);
        }
        if (array_key_exists('anyOf', $arr)) {
            return AnyOf::parse($arr);
        }

        $type = array_key_exists('type', $arr) ? $arr['type'] : 'object';
        return match ($type) {
            'boolean' => BoolType::parse($arr),
            'array' => ArrayType::parse($arr),
            'integer' => IntegerType::parse($arr),
            'number' => NumberType::parse($arr),
            'string' => StringType::parse($arr),
            'null' => NullType::parse($arr),
            'object' => ObjectType::parse($arr),
        };
    }

}
