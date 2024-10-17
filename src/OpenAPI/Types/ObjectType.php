<?php

namespace TinyFramework\OpenAPI\Types;

use stdClass;
use TinyFramework\OpenAPI\Objects\AbstractObject;
use TinyFramework\OpenAPI\Objects\Schema;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Settings\DiscriminatorSettings;
use TinyFramework\OpenAPI\Settings\XMLSettings;

/**
 * @alias object
 */
class ObjectType extends AbstractType
{

    public string $type = 'object';
    public bool $nullable = false;
    public array $required = [];
    public ?int $minProperties = null;
    public ?int $maxProperties = null;
    /** @var null|object<string, AbstractType> */
    public ?object $properties = null;
    public bool|AbstractType $additionalProperties = true;
    public ?XMLSettings $xml = null;
    public ?DiscriminatorSettings $discriminator = null;

    public static function parse(array $arr): ObjectType
    {
        $object = new ObjectType();
        if (array_key_exists('nullable', $arr)) {
            $object->nullable = (bool)$arr['nullable'];
        }
        if (array_key_exists('required', $arr)) {
            $object->required = array_map(fn(string $field) => (string)$field, $arr['required']);
        }
        if (array_key_exists('properties', $arr)) {
            $object->properties = new stdClass();
            foreach ($arr['properties'] as $key => $schema) {
                $object->properties->{$key} = Schema::parse($schema);
            }
        }
        if (array_key_exists('additionalProperties', $arr)) {
            if (is_bool($arr['additionalProperties'])) {
                $object->additionalProperties = $arr['additionalProperties'];
            } else {
                $object->additionalProperties = Schema::parse($arr['additionalProperties']);
            }
        }
        if (array_key_exists('minProperties', $arr)) {
            $object->minProperties = (int)$arr['minProperties'];
        }
        if (array_key_exists('maxProperties', $arr)) {
            $object->maxProperties = (int)$arr['maxProperties'];
        }
        if (array_key_exists('xml', $arr)) {
            $object->xml = XMLSettings::parse($arr['xml']);
        }
        if (array_key_exists('discriminator', $arr)) {
            $object->discriminator = DiscriminatorSettings::parse($arr['discriminator']);
        }
        return $object->parseExtension($arr);
    }

    public function validate(mixed $value): void
    {
        if ($this->nullable && $value === null) {
            return;
        }
        if (is_array($value)) {
            $value = (object)$value;
        }
        if (!is_object($value)) {
            throw new OpenAPIException('Invalid object.');
        }
        $additionalProperties = clone $value;
        if ($this->properties) {
            foreach ($this->properties as $key => $scheme) {
                if (property_exists($additionalProperties, $key)) {
                    $scheme->validate($additionalProperties->{$key});
                    unset($additionalProperties->{$key});
                }
            }
        }
        if ($this->additionalProperties === false) {
            if (count(get_object_vars($additionalProperties))) {
                throw new OpenAPIException('No additional properties are allowed.');
            }
        } elseif ($this->additionalProperties instanceof Schema) {
            foreach ($additionalProperties as $subValue) {
                $this->additionalProperties->validate($subValue);
            }
        }
        if ($this->required) {
            foreach ($this->required as $key) {
                if (!property_exists($value, $key)) {
                    throw new OpenAPIException('Missing required property ' . $key . '.');
                }
            }
        }
        if ($this->minProperties !== null || $this->maxProperties !== null) {
            $count = count(get_object_vars($value));
            if ($this->minProperties !== null && $count < $this->minProperties) {
                throw new OpenAPIException(
                    'Invalid property count. Required minimal ' . $this->minProperties . ' properties.'
                );
            }
            if ($this->maxProperties !== null && $count > $this->maxProperties) {
                throw new OpenAPIException(
                    'Invalid property count. Required maximal ' . $this->maxProperties . ' properties.'
                );
            }
        }
        // @TODO validate discriminator
    }

}
