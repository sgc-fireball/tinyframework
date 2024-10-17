<?php

namespace TinyFramework\OpenAPI\Objects;

use stdClass;

class OAuthFlow extends AbstractObject
{

    public string $authorizationUrl; // @todo only for: implicit, authorizationCode
    public string $tokenUrl; // @todo only for: password, clientCredentials, authorizationCode
    public ?string $refreshUrl = null;

    /** @var ?object<string, string> */
    public ?object $scopes = null;

    public static function parse(array $arr): OAuthFlow
    {
        $object = new OAuthFlow();
        if (array_key_exists('authorizationUrl', $arr)) {
            $object->authorizationUrl = $arr['authorizationUrl'];
        }
        if (array_key_exists('tokenUrl', $arr)) {
            $object->tokenUrl = $arr['tokenUrl'];
        }
        if (array_key_exists('refreshUrl', $arr)) {
            $object->refreshUrl = $arr['refreshUrl'];
        }
        if (!array_key_exists('scopes', $arr) || !is_array($arr['scopes'])) {
            throw new \InvalidArgumentException('OAuthFlow::scopes is missing');
        }
        $object->scopes = new stdClass();
        foreach ($arr['scopes'] as $scope => $description) {
            $object->scopes[$scope] = $description;
        }
        return $object->parseExtension($arr);
    }
}
