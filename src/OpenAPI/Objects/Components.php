<?php

namespace TinyFramework\OpenAPI\Objects;

use stdClass;
use TinyFramework\OpenAPI\Types\AbstractType;

/**
 * @link https://swagger.io/specification/#components-object
 */
class Components extends AbstractObject
{

    /** @var null|stdClass<Schema> */
    public ?object $schemas = null;

    /** @var null|stdClass<Response|Reference> */
    public ?object $responses = null;

    /** @var null|stdClass<Parameter|Reference> */
    public ?object $parameters = null;

    /** @var null|stdClass<Example|Reference> */
    public ?object $examples = null;

    /** @var null|stdClass<RequestBody|Reference> */
    public ?object $requestBodies = null;

    /** @var null|stdClass<Header|Reference> */
    public null|stdClass $headers = null;

    /** @var null|stdClass<SecurityScheme|Reference> */
    public ?object $securitySchemes = null;

    /** @var null|stdClass<Link|Reference> */
    public ?object $links = null;

    /** @var null|stdClass<Callback|Reference> */
    public ?object $callbacks = null;

    /** @var null|stdClass<PathItem|Reference> */
    public ?object $pathItems = null;

    public static function parse(array $arr): Components
    {
        $object = new Components();
        if (array_key_exists('schemas', $arr)) {
            $object->schemas = new stdClass();
            foreach ($arr['schemas'] as $key => $schema) {
                if (array_key_exists('$ref', $schema)) {
                    $object->schemas->{$key} = Reference::parse($schema);
                } else {
                    $object->schemas->{$key} = AbstractType::parse($schema);
                }
            }
        }
        if (array_key_exists('responses', $arr)) {
            $object->responses = new stdClass();
            foreach ($arr['responses'] as $key => $responses) {
                if (array_key_exists('$ref', $responses)) {
                    $object->responses->{$key} = Reference::parse($responses);
                } else {
                    $object->responses->{$key} = Response::parse($responses);
                }
            }
        }
        if (array_key_exists('parameters', $arr)) {
            $object->parameters = new stdClass();
            foreach ($arr['parameters'] as $key => $parameter) {
                if (array_key_exists('$ref', $parameter)) {
                    $object->parameters->{$key} = Reference::parse($parameter);
                } else {
                    $object->parameters->{$key} = Parameter::parse($parameter);
                }
            }
        }
        if (array_key_exists('examples', $arr)) {
            $object->examples = new stdClass();
            foreach ($arr['examples'] as $key => $example) {
                if (array_key_exists('$ref', $example)) {
                    $object->examples->{$key} = Reference::parse($example);
                } else {
                    $object->examples->{$key} = Example::parse($example);
                }
            }
        }
        if (array_key_exists('requestBodies', $arr)) {
            $object->requestBodies = new stdClass();
            foreach ($arr['requestBodies'] as $key => $requestBody) {
                if (array_key_exists('$ref', $requestBody)) {
                    $object->requestBodies->{$key} = Reference::parse($requestBody);
                } else {
                    $object->requestBodies->{$key} = RequestBody::parse($requestBody);
                }
            }
        }
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
        if (array_key_exists('securitySchemes', $arr)) {
            $object->securitySchemes = new stdClass();
            foreach ($arr['securitySchemes'] as $key => $securityScheme) {
                if (array_key_exists('$ref', $securityScheme)) {
                    $object->securitySchemes->{$key} = Reference::parse($securityScheme);
                } else {
                    $object->securitySchemes->{$key} = SecurityScheme::parse($securityScheme);
                }
            }
        }
        if (array_key_exists('links', $arr)) {
            $object->links = new stdClass();
            foreach ($arr['links'] as $key => $link) {
                if (array_key_exists('$ref', $link)) {
                    $object->links->{$key} = Reference::parse($link);
                } else {
                    $object->links->{$key} = Link::parse($link);
                }
            }
        }
        if (array_key_exists('callbacks', $arr)) {
            $object->callbacks = new stdClass();
            foreach ($arr['callbacks'] as $key => $callback) {
                if (array_key_exists('$ref', $callback)) {
                    $object->callbacks->{$key} = Reference::parse($callback);
                } else {
                    $object->callbacks->{$key} = Callback::parse($callback);
                }
            }
        }
        if (array_key_exists('pathItems', $arr)) {
            $object->pathItems = new stdClass();
            foreach ($arr['pathItems'] as $key => $pathItem) {
                if (array_key_exists('$ref', $pathItem)) {
                    $object->pathItems->{$key} = Reference::parse($pathItem);
                } else {
                    $object->pathItems->{$key} = PathItem::parse($pathItem);
                }
            }
        }
        return $object->parseExtension($arr);
    }

}
