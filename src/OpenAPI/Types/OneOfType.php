<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Objects\Reference;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Settings\DiscriminatorSettings;
use TinyFramework\OpenAPI\Settings\XMLSettings;

class OneOfType extends AbstractType
{

    public string $type = 'oneOf';
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
