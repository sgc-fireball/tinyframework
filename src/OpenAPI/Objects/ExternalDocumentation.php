<?php

namespace TinyFramework\OpenAPI\Objects;

class ExternalDocumentation extends AbstractObject
{

    public ?string $description = null;
    public ?string $url = null;

    public static function parse(array $arr): ExternalDocumentation
    {
        $object = new ExternalDocumentation();
        if (filter_var($arr['url'], FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('ExternalDocumentation::url must be a valid url.');
        }
        $object->url = $arr['url'];
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        return $object->parseExtension($arr);
    }

}
