<?php

namespace TinyFramework\OpenAPI\Settings;

use TinyFramework\OpenAPI\Objects\AbstractObject;

class XMLSettings extends AbstractObject
{

    public ?string $name = null;
    public ?string $namespace = null;
    public ?string $prefix = null;
    public bool $attribute = false;
    public bool $wrapped = false;

    public static function parse(array $arr): XMLSettings
    {
        $object = new XMLSettings();
        if (array_key_exists('name', $arr)) {
            $object->name = $arr['name'];
        }
        if (array_key_exists('namespace', $arr)) {
            $object->namespace = $arr['namespace'];
        }
        if (array_key_exists('prefix', $arr)) {
            $object->prefix = $arr['prefix'];
        }
        if (array_key_exists('attribute', $arr)) {
            $object->attribute = (bool)$arr['attribute'];
        }
        if (array_key_exists('wrapped', $arr)) {
            $object->wrapped = (bool)$arr['wrapped'];
        }
        return $object->parseExtension($arr);
    }
}
