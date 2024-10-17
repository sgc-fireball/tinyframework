<?php

namespace TinyFramework\OpenAPI\Objects;

class Example extends AbstractObject
{

    public ?string $summary = null;
    public ?string $description = null;
    public mixed $value = null;
    public mixed $externalValue = null;

    public static function parse(array $arr): Example
    {
        $object = new Example();
        if (array_key_exists('summary', $arr)) {
            $object->summary = $arr['summary'];
        }
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        if (array_key_exists('value', $arr)) {
            $object->value = $arr['value'];
        }
        if (array_key_exists('externalValue', $arr)) {
            $object->externalValue = $arr['externalValue'];
        }
        return $object->parseExtension($arr);
    }
}
