<?php

namespace TinyFramework\OpenAPI\Objects;

use TinyFramework\OpenAPI\SecuritySchemeIn;
use TinyFramework\OpenAPI\SecuritySchemeType;

class SecurityScheme extends AbstractObject
{

    public SecuritySchemeType $type;
    public ?string $description = null;
    public ?string $name = null;
    public ?SecuritySchemeIn $in = null;
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
        if (!array_key_exists('type', $arr) || !SecuritySchemeType::from($arr['type'])) {
            throw new \InvalidArgumentException(
                'SecurityScheme::type must be defined and one of:: apiKey, http, mutualTLS, oauth2, openIdConnect'
            );
        }
        $object->type = SecuritySchemeType::from($arr['type']);
        if (array_key_exists('description', $arr)) {
            $object->description = $arr['description'];
        }

        if ($object->type === SecuritySchemeType::API_KEY) {
            if (!array_key_exists('name', $arr) || !$arr['name']) {
                throw new \InvalidArgumentException('SecurityScheme::name is missing.');
            }
            if (!array_key_exists('in', $arr) || !SecuritySchemeIn::tryFrom($arr['in'])) {
                throw new \InvalidArgumentException(
                    'SecurityScheme::in must be defined and one of: query, header, cookie'
                );
            }
            $object->name = $arr['name'];
            $object->in = SecuritySchemeIn::from($arr['in']);
        }

        if ($object->type === SecuritySchemeType::HTTP && !array_key_exists('scheme', $arr)) {
            throw new \InvalidArgumentException('SecurityScheme::scheme is missing.');
        }
        if ($object->type === SecuritySchemeType::OAUTH2 && !array_key_exists('flows', $arr)) {
            throw new \InvalidArgumentException('SecurityScheme::flows is missing.');
        }
        if ($object->type === SecuritySchemeType::OPEN_ID_CONNECT && !array_key_exists('openIdConnectUrl', $arr)) {
            throw new \InvalidArgumentException('SecurityScheme::openIdConnectUrl  is missing.');
        }

        if ($object->type === SecuritySchemeType::HTTP) {
            $object->scheme = $arr['scheme'];
            if ($object->scheme === 'bearer' && array_key_exists('bearerFormat', $arr)) {
                $object->bearerFormat = $arr['bearerFormat'];
            }
        }

        if ($object->type === SecuritySchemeType::OAUTH2) {
            $object->flows = OAuthFlows::parse($arr['flows']);
        }

        if ($object->type === SecuritySchemeType::OPEN_ID_CONNECT) {
            $object->openIdConnectUrl = $arr['openIdConnectUrl'];
        }
        return $object->parseExtension($arr);
    }

}
