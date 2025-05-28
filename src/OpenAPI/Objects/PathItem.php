<?php

namespace TinyFramework\OpenAPI\Objects;

/**
 * @link https://swagger.io/specification/#path-item-object
 */
class PathItem extends AbstractObject
{

    public ?string $summary = null;
    public ?string $description = null;

    public Operation|null $get = null;
    public Operation|null $put = null;
    public Operation|null $post = null;
    public Operation|null $delete = null;
    public Operation|null $options = null;
    public Operation|null $head = null;
    public Operation|null $patch = null;
    public Operation|null $trace = null;

    /** @var Server[] */
    public array $servers = [];

    /** @var Parameter[]|Reference[] */
    public array $parameters = [];

    public static function parse(array $arr): PathItem
    {
        $object = new PathItem();
        if (array_key_exists('summary', $arr)) {
            $object->summary = $arr['summary'];
        }
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        foreach (['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace'] as $method) {
            if (array_key_exists($method, $arr)) {
                $object->{$method} = Operation::parse($arr[$method]);
            }
        }
        if (array_key_exists('servers', $arr)) {
            $object->servers = array_map(fn(array $server) => Server::parse($server), $arr['servers']);
        }
        if (array_key_exists('parameters', $arr)) {
            $object->parameters = array_map(function(array $parameter) {
                if (array_key_exists('$ref', $parameter)) {
                    return Reference::parse($parameter);
                }
                return Parameter::parse($parameter);
            }, $arr['parameters']);
        }
        return $object->parseExtension($arr);
    }

}
