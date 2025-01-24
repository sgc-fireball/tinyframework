<?php

namespace TinyFramework\OpenAPI\Objects;

use TinyFramework\Http\RequestInterface;
use TinyFramework\OpenAPI\OpenAPIException;

/**
 * @link https://swagger.io/specification/#server-object
 */
class Server extends AbstractObject
{

    public string $url;
    public string $description;

    /**
     * @var ?object<string, ServerVariable>
     */
    public ?object $variables = null;

    public static function parse(array $arr): Server
    {
        $object = new Server();
        if (array_key_exists('url', $arr)) {
            $object->url = $arr['url'];
        }
        if (array_key_exists('description', $arr)) {
            $object->url = $arr['description'];
        }
        if (array_key_exists('variables', $arr)) {
            $object->variables = new \stdClass();
            foreach ($arr['variables'] as $key => $variable) {
                $object->variables->{$key} = ServerVariable::parse($variable);
            }
        }
        return $object->parseExtension($arr);
    }

}
