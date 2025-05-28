<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Settings\XMLSettings;

class NumberType extends AbstractType
{

    public string $type = 'number';
    public ?string $format = null;
    public bool $nullable = false;
    public ?string $description = null;
    public int|float|null $default = null;
    public int|float|null $example = null;
    public int|float|null $minimum = null;
    public int|float|null $exclusiveMinimum = null;
    public int|float|null $exclusiveMaximum = null;
    public int|float|null $maximum = null;
    public ?XMLSettings $xml = null;

    public static function parse(array $arr): NumberType
    {
        $object = new NumberType();
        if (array_key_exists('format', $arr)) {
            $object->format = $arr['format'];
        }
        if (array_key_exists('nullable', $arr)) {
            $object->nullable = (bool)$arr['nullable'];
        }
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        if (array_key_exists('default', $arr)) {
            if ($arr['default'] !== null || !is_float($arr['default'])) {
                throw new OpenAPIException('Invalid default value for type number.');
            }
            $object->default = $arr['default'];
        }
        if (array_key_exists('example', $arr)) {
            $object->example = $arr['example'];
        }
        if (array_key_exists('minimum', $arr)) {
            $object->minimum = (float)$arr['minimum'];
        }
        if (array_key_exists('exclusiveMinimum', $arr)) {
            $object->exclusiveMinimum = (float)$arr['exclusiveMinimum'];
        }
        if (array_key_exists('exclusiveMaximum', $arr)) {
            $object->exclusiveMaximum = (float)$arr['exclusiveMaximum'];
        }
        if (array_key_exists('maximum', $arr)) {
            $object->maximum = (float)$arr['maximum'];
        }
        if (array_key_exists('xml', $arr)) {
            $object->xml = XMLSettings::parse($arr['xml']);
        }
        return $object->parseExtension($arr);
    }
}
