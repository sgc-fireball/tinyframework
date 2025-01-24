<?php

namespace TinyFramework\OpenAPI\Objects;

use TinyFramework\OpenAPI\OpenAPIException;

/**
 * @link https://swagger.io/specification/#contact-object
 */
class Contact extends AbstractObject
{

    public ?string $name = null;
    public ?string $url = null;
    public ?string $email = null;

    public static function parse(array $arr): Contact
    {
        $object = new Contact();
        if (array_key_exists('name', $arr)) {
            $object->name = $arr['name'];
        }
        if (array_key_exists('url', $arr)) {
            if (!filter_var($arr['url'], FILTER_VALIDATE_URL)) {
                throw new OpenAPIException('Invalid contact.url.');
            }
            $object->url = $arr['url'];
        }
        if (array_key_exists('email', $arr)) {
            if (!filter_var($arr['email'], FILTER_VALIDATE_EMAIL)) {
                throw new OpenAPIException('Invalid contact.email.');
            }
            $object->email = $arr['email'];
        }
        return $object->parseExtension($arr);
    }

}
