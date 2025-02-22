<?php

namespace TinyFramework\OpenAPI\Settings;

use stdClass;
use TinyFramework\OpenAPI\Objects\AbstractObject;

class DiscriminatorSettings extends AbstractObject
{

    public ?string $propertyName = null;
    /** @var null|stdClass<string> */
    public ?object $mapping = null;

    public static function parse(array $arr): DiscriminatorSettings
    {
        $object = new DiscriminatorSettings();
        if (!array_key_exists('propertyName', $arr)) {
            throw new \InvalidArgumentException('Discriminator::propertyName is missing.');
        }
        $object->propertyName = $arr['propertyName'];
        if (array_key_exists('mapping', $arr)) {
            $object->mapping = new stdClass();
            foreach ($arr['mapping'] as $key => $mapping) {
                $object->mapping->{$key} = (string)$mapping;
            }
        }
        $object = $object->parseExtension($arr);
        assert($object instanceof self);
        return $object;
    }
}
