<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Objects\AbstractObject;
use TinyFramework\OpenAPI\Objects\Reference;

abstract class AbstractType extends AbstractObject
{
    public static function parse(array $arr): AbstractType|Reference
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
            return AnyOfType::parse($arr);
        }

        $type = array_key_exists('type', $arr) ? (string)$arr['type'] : 'object';
        return match ($type) {
            'boolean' => BoolType::parse($arr),
            'array' => ArrayType::parse($arr),
            'integer' => IntegerType::parse($arr),
            'number' => NumberType::parse($arr),
            'string' => StringType::parse($arr),
            'null' => NullType::parse($arr),
            'object' => ObjectType::parse($arr),
            default => ObjectType::parse($arr),
        };
    }
}
