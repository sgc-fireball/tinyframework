<?php

namespace TinyFramework\OpenAPI\Types;

use stdClass;
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
                $object->properties->{$key} = AbstractType::parse($schema);
            }
        }
        if (array_key_exists('additionalProperties', $arr)) {
            if (is_bool($arr['additionalProperties'])) {
                $object->additionalProperties = $arr['additionalProperties'];
            } else {
                $object->additionalProperties = AbstractType::parse($arr['additionalProperties']);
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
}
