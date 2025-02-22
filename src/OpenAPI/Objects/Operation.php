<?php

namespace TinyFramework\OpenAPI\Objects;

use stdClass;

class Operation extends AbstractObject
{

    /** @var string[] */
    public array $tags = [];
    public ?string $summary = null;
    public ?string $description = null;
    public ExternalDocumentation|null $externalDocs = null;
    public ?string $operationId = null;

    /** @var Parameter[]|Reference[] */
    public array $parameters = [];

    public RequestBody|Reference|null $requestBody = null;

    /** @var null|stdClass<Response|Reference> */
    public ?object $responses = null;

    /** @var null|stdClass<Callback|Reference> */
    public ?object $callbacks = null;

    public bool $deprecated = false;

    /** @var SecurityRequirement[] */
    public array $security = [];

    /** @var Server[] */
    public array $servers = [];

    public static function parse(array $arr): Operation
    {
        $object = new Operation();
        if (array_key_exists('tags', $arr)) {
            $object->tags = array_map(fn(string $tag) => (string)$tag, $arr['tags']);
        }
        if (array_key_exists('summary', $arr)) {
            $object->summary = $arr['summary'];
        }
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }
        if (array_key_exists('externalDocs', $arr)) {
            $object->externalDocs = ExternalDocumentation::parse($arr['externalDocs']);
        }
        if (array_key_exists('operationId', $arr)) {
            $object->operationId = $arr['operationId'];
        }
        if (array_key_exists('parameters', $arr)) {
            $object->parameters = array_map(function (array $parameter) {
                if (array_key_exists('$ref', $parameter)) {
                    return Reference::parse($parameter);
                }
                return Parameter::parse($parameter);
            }, $arr['parameters']);
        }
        if (array_key_exists('requestBody', $arr)) {
            if (array_key_exists('$ref', $arr['requestBody'])) {
                $object->requestBody = Reference::parse($arr['requestBody']);
            } else {
                $object->requestBody = RequestBody::parse($arr['requestBody']);
            }
        }
        if (array_key_exists('responses', $arr)) {
            $object->responses = new stdClass();
            foreach ($arr['responses'] as $key => $response) {
                if (array_key_exists('$ref', $response)) {
                    $object->responses->{$key} = Reference::parse($response);
                } else {
                    $object->responses->{$key} = Response::parse($response);
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
        if (array_key_exists('deprecated', $arr)) {
            $object->deprecated = (bool)$arr['deprecated'];
        }
        if (array_key_exists('security', $arr)) {
            $object->security = array_map(function (array $security): SecurityRequirement {
                return SecurityRequirement::parse($security);
            }, $arr['security']);
        }
        if (array_key_exists('servers', $arr)) {
            $object->servers = array_map(fn(array $server) => Server::parse($server), $arr['servers']);
        }
        return $object->parseExtension($arr);
    }

}
