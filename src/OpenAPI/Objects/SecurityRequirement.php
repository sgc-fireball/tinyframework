<?php

namespace TinyFramework\OpenAPI\Objects;

use stdClass;
use TinyFramework\OpenAPI\OpenAPIException;

class SecurityRequirement extends AbstractObject
{

    /** @var object<string, array<int, string>> */
    public ?object $handler = null;

    /** @var object<string, SecurityScheme> */
    private ?object $_securitySchema = null;

    public static function parse(array $arr): SecurityRequirement
    {
        $object = new SecurityRequirement();
        $object->handler = new stdClass();
        $object->_securitySchema = new stdClass();
        foreach ($arr as $handler => $scopes) {
            $object->handler->{$handler} = array_map(fn(string $scope) => (string)$scope, $scopes);
            $object->_securitySchema->{$handler} = null;
        }
        $object = $object->parseExtension($arr);
        assert($object instanceof self);
        return $object;
    }

    public function setSecuritySchemaForHandler(string $handler, SecurityScheme $securitySchema): self
    {
        if (!property_exists($this->_securitySchema, $handler)) {
            throw new OpenAPIException('Invalid security handler.');
        }
        $this->_securitySchema->{$handler} = $securitySchema;
        return $this;
    }

    public function getSecuritySchemaByHandler(string $handler): SecurityScheme
    {
        if (!property_exists($this->_securitySchema, $handler)) {
            throw new OpenAPIException('Invalid security handler.');
        }
        return $this->_securitySchema?->{$handler};
    }

}
