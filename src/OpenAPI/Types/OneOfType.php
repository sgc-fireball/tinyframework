<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Objects\Reference;
use TinyFramework\OpenAPI\Objects\Schema;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Settings\DiscriminatorSettings;
use TinyFramework\OpenAPI\Settings\XMLSettings;

class OneOfType extends AbstractType
{

    /** @var AbstractType[]|Reference[] */
    public array $types = [];
    public ?XMLSettings $xml = null;
    public ?DiscriminatorSettings $discriminator = null;

    public static function parse(array $arr): OneOfType
    {
        $object = new OneOfType();
        if (!array_key_exists('oneOf', $arr) || !is_array($arr['oneOf'])) {
            throw new OpenAPIException('Missing oneOf field.');
        }
        foreach ($arr['oneOf'] as $schema) {
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
        $count = 0;
        foreach ($this->types as $type) {
            try {
                $type->validate($value);
                $count++;
            } catch (OpenAPIException $e) {
                // ignored
            }
        }
        if ($count !== 1) {
            throw new OpenAPIException('Invalid oneOf value.', 400);
        }
    }

}
