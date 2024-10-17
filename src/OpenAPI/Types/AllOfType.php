<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Objects\Reference;
use TinyFramework\OpenAPI\Objects\Schema;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Settings\DiscriminatorSettings;
use TinyFramework\OpenAPI\Settings\XMLSettings;

class AllOfType extends AbstractType
{

    /** @var AbstractType[]|Reference[] */
    public array $types = [];

    public ?XMLSettings $xml = null;
    public ?DiscriminatorSettings $discriminator = null;

    public static function parse(array $arr): AllOfType
    {
        $object = new AllOfType();
        if (!array_key_exists('allOf', $arr) || !is_array($arr['allOf'])) {
            throw new OpenAPIException('Missing allOf field.');
        }
        foreach ($arr['allOf'] as $schema) {
            $object->types[] = Schema::parse($schema);
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
        // @TODO discriminator
        foreach ($this->types as $type) {
            $type->validate($value);
        }
    }

}
