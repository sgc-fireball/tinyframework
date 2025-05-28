<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Settings\XMLSettings;

class IntegerType extends AbstractType
{

    public string $type = 'integer';
    public ?string $format = null;
    public bool $nullable = false;
    public ?string $description = null;
    public ?int $default = null;
    public ?int $example = null;
    public ?int $minimum = null;
    public ?int $maximum = null;
    public ?int $exclusiveMaximum = null;
    public ?int $exclusiveMinimum = null;
    public ?XMLSettings $xml = null;

    /**
     * @param array $arr
     * @return IntegerType
     */
    public static function parse(array $arr): IntegerType
    {
        $object = new IntegerType();
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
            $object->default = (int)$arr['default'];
        }
        if (array_key_exists('example', $arr)) {
            $object->example = (int)$arr['example'];
        }
        if (array_key_exists('minimum', $arr)) {
            $object->minimum = (int)$arr['minimum'];
        }
        if (array_key_exists('exclusiveMinimum', $arr)) {
            $object->exclusiveMinimum = (int)$arr['exclusiveMinimum'];
        }
        if (array_key_exists('exclusiveMaximum', $arr)) {
            $object->exclusiveMaximum = (int)$arr['exclusiveMaximum'];
        }
        if (array_key_exists('maximum', $arr)) {
            $object->maximum = (int)$arr['maximum'];
        }
        if (array_key_exists('xml', $arr)) {
            $object->xml = XMLSettings::parse($arr['xml']);
        }
        return $object->parseExtension($arr);
    }
}
