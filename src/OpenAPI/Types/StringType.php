<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Settings\XMLSettings;

class StringType extends AbstractType
{

    public string $type = 'string';
    public ?string $format = null;
    public bool $nullable = false;
    public ?string $description = null;
    public ?string $default = null;
    public ?string $example = null;
    public ?array $enum = null;
    public ?int $minLength = null;
    public ?int $maxLength = null;
    public ?string $pattern = null;
    public ?XMLSettings $xml = null;

    public static function parse(array $arr): StringType
    {
        $object = new StringType();
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
            $object->default = $arr['default'];
        }
        if (array_key_exists('example', $arr)) {
            $object->default = $arr['example'];
        }
        if (array_key_exists('minLength', $arr)) {
            $object->minLength = (int)$arr['minLength'];
        }
        if (array_key_exists('maxLength', $arr)) {
            $object->maxLength = (int)$arr['maxLength'];
        }
        if (array_key_exists('pattern', $arr)) {
            $object->pattern = $arr['pattern'];
        }
        if (array_key_exists('enum', $arr)) {
            $object->enum = arraY_map(fn(string $enum) => (string)$enum, $arr['enum']);
        }
        if (array_key_exists('xml', $arr)) {
            $object->xml = XMLSettings::parse($arr['xml']);
        }
        return $object->parseExtension($arr);
    }
}
