<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Objects\Reference;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Settings\DiscriminatorSettings;
use TinyFramework\OpenAPI\Settings\XMLSettings;

class AllOfType extends AbstractType
{

    public string $type = 'allOf';

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
            $object->types[] = AbstractType::parse($schema);
        }
        if (array_key_exists('xml', $arr)) {
            $object->xml = XMLSettings::parse($arr['xml']);
        }
        if (array_key_exists('discriminator', $arr)) {
            $object->discriminator = DiscriminatorSettings::parse($arr['discriminator']);
        }
        $object = $object->parseExtension($arr);
        assert($object instanceof self);
        return $object;
    }
}
