<?php

namespace TinyFramework\OpenAPI\Types;

use TinyFramework\OpenAPI\Objects\Reference;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Settings\DiscriminatorSettings;
use TinyFramework\OpenAPI\Settings\XMLSettings;

class AnyOfType extends AbstractType
{

    public string $type = 'anyOf';
    /** @var AbstractType[]|Reference[] */
    public array $types = [];
    public ?XMLSettings $xml = null;
    public ?DiscriminatorSettings $discriminator = null;

    public static function parse(array $arr): AnyOfType
    {
        $object = new AnyOfType();
        if (!array_key_exists('anyOf', $arr) || !is_array($arr['anyOf'])) {
            throw new OpenAPIException('Missing anyOf field.');
        }
        foreach ($arr['anyOf'] as $schema) {
            $object->types[] = AbstractType::parse($schema);
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
