<?php

namespace TinyFramework\OpenAPI\Objects;

use TinyFramework\OpenAPI\OpenAPIException;

/**
 * @see https://swagger.io/specification/#license-object
 */
class License extends AbstractObject
{

    public string $name;
    public ?string $identifier = null;
    public ?string $url = null;

    public static function parse(array $arr): License
    {
        $object = new License();
        if (!array_key_exists('name', $arr) || !$arr['name']) {
            throw new OpenAPIException('Invalid or missing license.name');
        }
        $object->name = $arr['name'];
        if (array_key_exists('identifier', $arr)) {
            $object->identifier = $arr['identifier'];
        }
        if (array_key_exists('url', $arr)) {
            if (!filter_var($arr['url'], FILTER_VALIDATE_URL)) {
                throw new OpenAPIException('Invalid license.url');
            }
            $object->url = $arr['url'];
        }
        return $object->parseExtension($arr);
    }

}
