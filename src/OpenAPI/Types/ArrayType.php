<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Objects\Reference;
use TinyFramework\OpenAPI\Settings\XMLSettings;

/**
 * @alias array
 */
class ArrayType extends AbstractType
{

    public string $type = 'array';
    public bool $nullable = false;
    public ?int $minItems = null;
    public ?int $maxItems = null;
    public bool $uniqueItems = false;
    public AbstractType|Reference|null $items = null;
    public ?XMLSettings $xml = null;

    public static function parse(array $arr): ArrayType
    {
        $object = new ArrayType();
        if (array_key_exists('nullable', $arr)) {
            $object->nullable = (bool)$arr['nullable'];
        }
        if (array_key_exists('uniqueItems', $arr)) {
            $object->uniqueItems = (bool)$arr['uniqueItems'];
        }
        if (array_key_exists('minItems', $arr)) {
            $object->minItems = (int)$arr['minItems'];
        }
        if (array_key_exists('maxItems', $arr)) {
            $object->maxItems = (int)$arr['maxItems'];
        }
        if (array_key_exists('items', $arr)) {
            $object->items = AbstractType::parse($arr['items']);
        }
        if (array_key_exists('xml', $arr)) {
            $object->xml = XMLSettings::parse($arr['xml']);
        }
        return $object->parseExtension($arr);
    }
}
