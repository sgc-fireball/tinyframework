<?php

namespace TinyFramework\OpenAPI\Objects;

use TinyFramework\Http\RequestInterface;
use TinyFramework\OpenAPI\OpenAPIException;

class SecurityScheme extends AbstractObject
{

    public string $type; // enum: apiKey, http, mutualTLS, oauth2, openIdConnect
    public ?string $description = null;
    public ?string $name = null;
    public ?string $in = null; // enum: query, header, cookie
    public string $scheme = 'bearer';
    public string $bearerFormat = 'jwt';
    public OAuthFlows|null $flows = null;
    public ?string $openIdConnectUrl = '';


    /**
     * @TODO implement seperate classes for different security scheme types
     */
    public static function parse(array $arr): SecurityScheme
    {
        $object = new SecurityScheme();
        if (!array_key_exists('type', $arr) || !$arr['type']) {
            throw new \InvalidArgumentException('SecurityScheme::type is missing.');
        }
        if (!in_array($arr['type'], ['apiKey', 'http', 'mutualTLS', 'oauth2', 'openIdConnect'])) {
            throw new \InvalidArgumentException(
                'SecurityScheme::type is invalid. Valid values are: apiKey, http, mutualTLS, oauth2, openIdConnect'
            );
        }
        $object->type = $arr['type'];
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }

        if ($object->type === 'apiKey') {
            if (!array_key_exists('name', $arr) || !$arr['name']) {
                throw new \InvalidArgumentException('SecurityScheme::name is missing.');
            }
            if (!array_key_exists('in', $arr) || !$arr['in']) {
                throw new \InvalidArgumentException('SecurityScheme::in is missing.');
            }
            if (!in_array($arr['in'], ['query', 'header', 'cookie'])) {
                throw new \InvalidArgumentException(
                    'SecurityScheme::in is invalid. Valid values are: query, header, cookie'
                );
            }
            $object->name = $arr['name'];
            $object->in = $arr['in'];
        }

        if ($object->type === 'http' && !array_key_exists('scheme', $arr)) {
            throw new \InvalidArgumentException('SecurityScheme::scheme is missing.');
        }
        if ($object->type === 'oauth2' && !array_key_exists('flows', $arr)) {
            throw new \InvalidArgumentException('SecurityScheme::flows is missing.');
        }
        if ($object->type === 'openIdConnect' && !array_key_exists('openIdConnectUrl', $arr)) {
            throw new \InvalidArgumentException('SecurityScheme::openIdConnectUrl  is missing.');
        }

        if ($object->type === 'http') {
            $object->scheme = $arr['scheme'];
            if ($object->scheme === 'bearer' && array_key_exists('bearerFormat', $arr)) {
                $object->bearerFormat = $arr['bearerFormat'];
            }
        }

        if ($object->type === 'oauth2') {
            $object->flows = OAuthFlows::parse($arr['flows']);
        }

        if ($object->type === 'openIdConnect') {
            $object->openIdConnectUrl = $arr['openIdConnectUrl'];
        }
        return $object->parseExtension($arr);
    }

}
