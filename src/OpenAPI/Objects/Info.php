<?php

namespace TinyFramework\OpenAPI\Objects;

use TinyFramework\OpenAPI\OpenAPIException;

/**
 * @see https://swagger.io/specification/#info-object
 */
class Info extends AbstractObject
{

    public string $title;
    public ?string $summary = null;
    public ?string $description = null;
    public ?string $termsOfService = null;
    public Contact|null $contact = null;
    public License|null $license = null;
    public string $version = '3.1.0';

    public static function parse(array $arr): Info
    {
        $object = new Info();
        if (!array_key_exists('title', $arr) || !$arr['title']) {
            throw new OpenAPIException('Missing required info.title.');
        }
        $object->title = $arr['title'];
        if (!array_key_exists('version', $arr) || !$arr['version']) {
            throw new OpenAPIException('Missing required info.version.');
        }
        $object->version = $arr['version'];
        if (array_key_exists('summary', $arr)) {
            $object->summary = $arr['summary'];
        }
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        if (array_key_exists('termsOfService', $arr)) {
            if (!filter_var($arr['termsOfService'], FILTER_VALIDATE_URL)) {
                throw new OpenAPIException('Invalid terms of service url.');
            }
            $object->termsOfService = $arr['termsOfService'];
        }
        if (array_key_exists('contact', $arr)) {
            $object->contact = Contact::parse($arr['contact']);
        }
        if (array_key_exists('license', $arr)) {
            $object->license = License::parse($arr['license']);
        }
        return $object->parseExtension($arr);
    }

}
