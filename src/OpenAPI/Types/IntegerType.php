<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Objects\AbstractObject;
use TinyFramework\OpenAPI\Objects\Schema;
use TinyFramework\OpenAPI\OpenAPIException;
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

    public function validate(mixed $value): void
    {
        if ($this->nullable && $value === null) {
            return;
        }
        if (!is_int($value)) {
            throw new OpenAPIException('Invalid integer value.', 400);
        }
        if (!is_null($this->minimum) && $value < $this->minimum) {
            throw new OpenAPIException(
                'Invalid integer, value is to low (inclusive minimum: .' . $this->minimum . ').',
                400
            );
        }
        if (!is_null($this->exclusiveMinimum) && $value <= $this->exclusiveMinimum) {
            throw new OpenAPIException(
                'Invalid integer, value is to low (exclusive minimum: .' . $this->exclusiveMinimum . ').', 400
            );
        }
        if (!is_null($this->exclusiveMaximum) && $value >= $this->exclusiveMaximum) {
            throw new OpenAPIException(
                'Invalid integer, value is to high (exclusive maximum: .' . $this->exclusiveMaximum . ').', 400
            );
        }
        if (!is_null($this->maximum) && $value > $this->maximum) {
            throw new OpenAPIException(
                'Invalid integer, value is to high (inclusive maximum: .' . $this->maximum . ').', 400
            );
        }
    }

}
