<?php

namespace TinyFramework\OpenAPI\Objects;

use stdClass;

/**
 * @link https://swagger.io/specification/#responses-object
 */
class Response extends AbstractObject
{

    public string $description = '';
    /** @var ?object<string, Header|Reference> */
    public ?object $headers = null;
    /** @var ?object<string, Mediatype> */
    public ?object $content = null;
    /** @var ?object<string, Link|Reference> */
    public ?object $links = null;

    public static function parse(array $arr): Response
    {
        $object = new Response();
        if (!array_key_exists('description', $arr) || !$arr['description']) {
            throw new \InvalidArgumentException('Response::description is missing');
        }
        $object->description = $arr['description'];
        if (array_key_exists('headers', $arr)) {
            $object->headers = new stdClass();
            foreach ($arr['headers'] as $key => $header) {
                if (array_key_exists('$ref', $header)) {
                    $object->headers->{$key} = Reference::parse($header);
                } else {
                    $object->headers->{$key} = Header::parse($header);
                }
            }
        }
        if (array_key_exists('content', $arr)) {
            $object->content = new stdClass();
            foreach ($arr['content'] as $key => $content) {
                $object->content->{$key} = MediaType::parse($content);
            }
        }
        if (array_key_exists('links', $arr)) {
            $object->links = new stdClass();
            foreach ($arr['links'] as $key => $link) {
                if (array_key_exists('$ref', $link)) {
                    $object->links->{$key} = Reference::parse($link);
                } else {
                    $object->links->{$key} = Header::parse($link);
                }
            }
        }
        return $object->parseExtension($arr);
    }

}
