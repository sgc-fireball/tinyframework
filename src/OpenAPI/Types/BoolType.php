<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Settings\XMLSettings;

class BoolType extends AbstractType
{

    public string $type = 'boolean';
    public bool $nullable = false;
    public ?string $description = null;
    public ?bool $default = null;
    public ?bool $example = null;
    public ?XMLSettings $xml = null;

    /**
     * @param array $arr
     * @return BoolType
     */
    public static function parse(array $arr): BoolType
    {
        $object = new BoolType();
        if (array_key_exists('nullable', $arr)) {
            $object->nullable = (bool)$arr['nullable'];
        }
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        if (array_key_exists('default', $arr)) {
            $object->default = (bool)$arr['default'];
        }
        if (array_key_exists('example', $arr)) {
            $object->example = (bool)$arr['example'];
        }
        if (array_key_exists('xml', $arr)) {
            $object->xml = XMLSettings::parse($arr['xml']);
        }
        return $object->parseExtension($arr);
    }
}
