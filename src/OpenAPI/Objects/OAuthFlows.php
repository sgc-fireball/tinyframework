<?php

namespace TinyFramework\OpenAPI\Objects;

/**
 * @link https://swagger.io/specification/#oauth-flows-object
 */
class OAuthFlows extends AbstractObject
{

    public OAuthFlow|null $implicit = null;
    public OAuthFlow|null $password = null;
    public OAuthFlow|null $clientCredentials = null;
    public OAuthFlow|null $authorizationCode = null;

    public static function parse(array $arr): OAuthFlows
    {
        $object = new OAuthFlows();
        if (array_key_exists('implicit', $arr)) {
            $object->implicit = OAuthFlow::parse($arr['implicit']);
        }
        if (array_key_exists('password', $arr)) {
            $object->password = OAuthFlow::parse($arr['password']);
        }
        if (array_key_exists('clientCredentials', $arr)) {
            $object->clientCredentials = OAuthFlow::parse($arr['clientCredentials']);
        }
        if (array_key_exists('authorizationCode', $arr)) {
            $object->authorizationCode = OAuthFlow::parse($arr['authorizationCode']);
        }
        $object = $object->parseExtension($arr);
        assert($object instanceof self);
        return $object;
    }
}
