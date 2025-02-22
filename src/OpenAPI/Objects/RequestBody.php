<?php

namespace TinyFramework\OpenAPI\Objects;

use stdClass;
use TinyFramework\Http\RequestInterface;
use TinyFramework\OpenAPI\OpenAPIException;

/**
 * @link https://swagger.io/specification/#request-body-object
 */
class RequestBody extends AbstractObject
{

    public ?string $description = null;
    public bool $required = false;
    /** @var null|stdClass<MediaType> */
    public ?object $content = null;

    public static function parse(array $arr): RequestBody
    {
        $object = new RequestBody();
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        if (array_key_exists('required', $arr)) {
            $object->required = (bool)$arr['required'];
        }
        if (!array_key_exists('content', $arr) || !is_array($arr['content']) || count($arr['content']) < 1) {
            throw new \InvalidArgumentException('RequestBody::content is invalid.');
        }
        $object->content = new stdClass();
        foreach ($arr['content'] as $key => $content) {
            $object->content->{$key} = MediaType::parse($content);
        }
        $object = $object->parseExtension($arr);
        assert($object instanceof self);
        return $object;
    }

}
