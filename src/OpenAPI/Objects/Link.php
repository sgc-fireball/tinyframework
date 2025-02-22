<?php

namespace TinyFramework\OpenAPI\Objects;

use stdClass;

/**
 * @link https://swagger.io/specification/#license-object
 */
class Link extends AbstractObject
{

    public ?string $operationRef;
    public ?string $operationId = null;
    /** @var null|stdClass<mixed> */
    public ?object $parameters = null;
    public ?string $requestBody = null;
    public ?string $description = null;

    /** @var Server */
    public Server|null $server = null;

    public static function parse(array $arr): Link
    {
        $object = new Link();
        if (array_key_exists('operationRef', $arr)) {
            $object->operationRef = $arr['operationRef'];
        }
        if (array_key_exists('operationId', $arr)) {
            $object->operationId = $arr['operationId'];
        }
        if (array_key_exists('parameters', $arr)) {
            $object->parameters = new stdClass();
            foreach ($arr['parameters'] as $key => $parameter) {
                $object->parameters->{$key} = $parameter; // @TODO i need a todo? or ... its done? what the?
            }
        }
        if (array_key_exists('requestBody', $arr)) {
            $object->requestBody = $arr['requestBody']; // @TODO i need a todo? or ... its done? what the?
        }
        if (array_key_exists('server', $arr)) {
            $object->server = Server::parse($arr['server']);
        }
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        if (array_key_exists('server', $arr)) {
            $object->server = Server::parse($arr['server']);
        }
        $object = $object->parseExtension($arr);
        assert($object instanceof self);
        return $object;
    }
}
