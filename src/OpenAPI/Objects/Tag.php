<?php

namespace TinyFramework\OpenAPI\Objects;

/**
 * @link https://swagger.io/specification/#header-object
 */
class Tag extends AbstractObject
{

    public string $name;
    public ?string $description = null;
    public ExternalDocumentation|null $externalDocs = null;

    public static function parse(array $arr): Tag
    {
        $object = new Tag();
        if (!array_key_exists('name', $arr) || !$arr['name']) {
            throw new \InvalidArgumentException('Tag::name is missing.');
        }
        $object->name = $arr['name'];
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        if (array_key_exists('externalDocs', $arr)) {
            $object->externalDocs = ExternalDocumentation::parse($arr['externalDocs']);
        }
        $object = $object->parseExtension($arr);
        assert($object instanceof self);
        return $object;
    }
}
