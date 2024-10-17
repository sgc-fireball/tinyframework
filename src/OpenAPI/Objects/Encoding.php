<?php

namespace TinyFramework\OpenAPI\Objects;

use stdClass;

/**
 * @see https://swagger.io/specification/#encoding-object
 */
class Encoding extends AbstractObject
{

    /** @var ?object<string, Header|Reference> */
    public ?object $headers = null;
    public string $contentType = 'application/octet-stream';
    public ?string $style = null;
    public bool $explode = false;
    public bool $allowReserved = false;

    public function __construct()
    {
        $this->headers = new stdClass();
    }

    public static function parse(array $arr): Encoding
    {
        $object = new Encoding();
        if (array_key_exists('explode', $arr)) {
            $object->explode = (bool)$arr['explode'];
        }
        if (array_key_exists('headers', $arr)) {
            foreach ($arr['headers'] as $key => $header) {
                if (array_key_exists('$ref', $header)) {
                    $object->headers->{$key} = Reference::parse($header);
                } else {
                    $object->headers->{$key} = Header::parse($header);
                }
            }
        }
        if (array_key_exists('contentType', $arr)) {
            $object->contentType = $arr['contentType'];
        }
        if (array_key_exists('style', $arr)) {
            $object->style = $arr['style'];
        }
        if (array_key_exists('allowReserved', $arr)) {
            $object->allowReserved = (bool)$arr['allowReserved'];
        }
        return $object->parseExtension($arr);
    }
}
