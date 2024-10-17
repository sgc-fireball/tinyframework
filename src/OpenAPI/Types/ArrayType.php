<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Objects\Reference;
use TinyFramework\OpenAPI\Objects\Schema;
use TinyFramework\OpenAPI\OpenAPIException;
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
            $object->items = Schema::parse($arr['items']);
        }
        if (array_key_exists('xml', $arr)) {
            $object->xml = XMLSettings::parse($arr['xml']);
        }
        return $object->parseExtension($arr);
    }

    public function validate(mixed $value): void
    {
        if ($this->nullable && $value === null) {
            return;
        }
        if (!is_array($value)) {
            throw new OpenAPIException('Invalid array value.', 400);
        }
        if ($this->minItems !== null || $this->maxItems !== null || $this->uniqueItems) {
            $count = count($value);
            if ($this->minItems !== null && $count < $this->minItems) {
                throw new OpenAPIException('Invalid array min count. Required ' . $this->minItems . ' items.', 400);
            }
            if ($this->maxItems !== null && $count > $this->maxItems) {
                throw new OpenAPIException('Invalid array max count. Required ' . $this->maxItems . ' items.', 400);
            }
            if ($this->uniqueItems && $count !== count(array_unique($value))) {
                throw new OpenAPIException('Invalid array. Array items are not unique.', 400);
            }
        }
        if (!$this->items) {
            return;
        }
        foreach ($value as $subValue) {
            $this->items->validate($subValue);
        }
    }

}
