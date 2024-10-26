<?php

namespace TinyFramework\OpenAPI;

use TinyFramework\Http\RequestInterface as HttpRequestInterface;
use TinyFramework\Http\Response as HttpResponse;
use TinyFramework\OpenAPI\ParameterIn;
use TinyFramework\OpenAPI\Objects\MediaType;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\Objects\Operation;
use TinyFramework\OpenAPI\Objects\Parameter;
use TinyFramework\OpenAPI\Objects\PathItem;
use TinyFramework\OpenAPI\Objects\Reference;
use TinyFramework\OpenAPI\Objects\RequestBody;
use TinyFramework\OpenAPI\Objects\Response;
use TinyFramework\OpenAPI\Objects\SecurityScheme;
use TinyFramework\OpenAPI\Objects\Server;
use TinyFramework\OpenAPI\Objects\ServerVariable;
use TinyFramework\OpenAPI\Types\AbstractType;
use TinyFramework\OpenAPI\Types\AllOfType;
use TinyFramework\OpenAPI\Types\AnyOfType;
use TinyFramework\OpenAPI\Types\ArrayType;
use TinyFramework\OpenAPI\Types\BoolType;
use TinyFramework\OpenAPI\Types\IntegerType;
use TinyFramework\OpenAPI\Types\NullType;
use TinyFramework\OpenAPI\Types\NumberType;
use TinyFramework\OpenAPI\Types\ObjectType;
use TinyFramework\OpenAPI\Types\OneOfType;
use TinyFramework\OpenAPI\Types\StringType;

class OpenAPIValidator
{

    public function __construct(
        private OpenAPI $openAPI
    ) {
    }

    public function validateHttpRequest(HttpRequestInterface $request): void
    {
        $realPath = $request->url()->path();
        if (!empty($this->openAPI->servers)) {
            foreach ($this->openAPI->servers as $server) {
                if ($this->validateServer($server, $request)) {
                    break;
                }
            }
            if (!$request->attribute('__openapi_server')) {
                throw new OpenAPIException('Invalid server prefix.', 404);
            }
        }

        /**
         * @var string $path
         * @var PathItem $pathItem
         */
        $realPath = $request->attribute('__openapi_realpath') ?? $realPath;
        if ($this->openAPI->paths) {
            $method = strtolower($request->method());
            foreach ($this->openAPI->paths as $path => $pathItem) {
                $regex = preg_replace_callback('(\{([^}]+)})', function (array $group) {
                    return '(?P<' . $group[1] . '>[^\/]+)';
                }, $path);
                $regex = '(^' . $regex . '$)';
                if (!preg_match($regex, $realPath, $matches)) {
                    continue;
                }
                if (!isset($pathItem->{$method})) {
                    throw new OpenAPIException('Method not allowed (1)', 405);
                }
                if (is_null($pathItem->{$method})) {
                    throw new OpenAPIException('Method not allowed (2)', 405);
                }
                $parameters = $request->attribute('__openapi_parameters') ?? [];
                foreach ($matches as $key => $value) {
                    if (!is_int(($key))) {
                        $parameters[$key] = $value;
                    }
                }
                $request->attribute('__openapi_parameters', $parameters);
                /** @var Operation $operation */
                $operation = $pathItem->{$method};
                $request->attribute('__openapi_operation', $operation);
                $this->validateOperationRequest($operation, $request);
                break;
            }
            if (!$request->attribute('__openapi_operation')) {
                throw new OpenAPIException('Endpoint not found', 406);
            }
        }
    }

    public function validateHttpResponse(HttpRequestInterface $httpRequest, HttpResponse $httpResponse): void
    {
        $operation = $httpRequest->attribute('__openapi_operation');
        if ($operation instanceof Operation) {
            $this->validateOperationResponse($operation, $httpRequest, $httpResponse);
        }
    }

    private function validateMediaTypeRequest(MediaType $mediaType, HttpRequestInterface $httpRequest): void
    {
        if (!$mediaType->schema) {
            return;
        }
        $this->validateSchema($mediaType->schema, $httpRequest->post());
    }

    private function validateMediaTypeResponse(MediaType $mediaType, mixed $value): void
    {
        if (!$mediaType->schema) {
            return;
        }
        $this->validateSchema($mediaType->schema, $value);
    }

    private function validateOperationRequest(Operation $operation, HttpRequestInterface $httpRequest): void
    {
        if ($operation->servers) {
            $httpRequest->attribute('__openapi_server', false);
            $httpRequest->attribute('__openapi_realpath', false);
            /** @var Server $server */
            foreach ($operation->servers as $server) {
                if ($this->validateServer($server, $httpRequest)) {
                    break;
                }
            }
            if (!$httpRequest->attribute('__openapi_server') || !$httpRequest->attribute('__openapi_realpath')) {
                throw new OpenAPIException('Invalid server entrypoint prefix.', 404);
            }
        }
        if ($operation->parameters) {
            foreach ($operation->parameters as $parameter) {
                $this->validateParameterRequest($parameter, $httpRequest);
            }
        }
        if ($operation->requestBody) {
            $this->validateRequestBody($operation->requestBody, $httpRequest);
        }
        if ($operation->security) {
            foreach ($operation->security as $securityRequirement) {
                foreach ($securityRequirement->handler as $handler => $scopes) {
                    $this->validateSecurityScheme(
                        $securityRequirement->getSecuritySchemaByHandler($handler),
                        $httpRequest
                    );
                }
            }
        }
    }

    private function validateOperationResponse(
        Operation $operation,
        HttpRequestInterface $httpRequest,
        HttpResponse $httpResponse
    ): void {
        if ($operation->responses === null) {
            return;
        }
        if (!property_exists($operation->responses, $httpResponse->code())) {
            throw new OpenAPIException('Invalid openapi response code.', 503);
        }
        /** @var Response|Reference $openAPIResponse */
        $openAPIResponse = $operation->responses->{$httpResponse->code()};
        $this->validateResponse($openAPIResponse, $httpResponse);
    }

    private function validateParameterRequest(Reference|Parameter $parameter, HttpRequestInterface $httpRequest): void
    {
        $value = match ($parameter->in) {
            ParameterIn::QUERY => $httpRequest->get($parameter->name) ?? null,
            ParameterIn::HEADER => $httpRequest->header($parameter->name)[0] ?? null,
            ParameterIn::PATH => ($httpRequest->attribute('__openapi_parameters') ?? [])[$parameter->name] ?? null,
            ParameterIn::COOKIE => $httpRequest->cookie($parameter->name) ?? null,
            default => throw new OpenAPIException('Unsupported Parameter::in type.', 500)
        };
        if ($parameter->required && $value === null) {
            throw new OpenAPIException('Missing value of ' . $parameter->name . ' in ' . $parameter->in->value, 400);
        }
        if (!$parameter->schema) {
            return;
        }
        $this->validateSchema($parameter->schema, $value);
    }

    public function validateRequestBody(Reference|RequestBody $requestBody, HttpRequestInterface $httpRequest): void
    {
        if ($requestBody->required && !$httpRequest->body() && count($httpRequest->post()) === 0) {
            throw new OpenAPIException('Missing request body.', 400);
        }
        if (!$requestBody->content) {
            return;
        }
        $contentType = explode(';', $httpRequest->header('content-type')[0], 2)[0];
        if (!property_exists($requestBody->content, $contentType)) {
            throw new OpenAPIException('Invalid request body content type.', 400);
        }
        /** @var MediaType $mediaType */
        $mediaType = $requestBody->content->{$contentType};
        $this->validateMediaTypeRequest($mediaType, $httpRequest);
    }

    private function validateResponse(Reference|Response $openAPIResponse, HttpResponse $httpResponse): void
    {
        $contentType = $httpResponse->header('content-type') ?? '';
        $contentType = explode(';', $contentType, 2)[0];
        if (!property_exists($openAPIResponse->content, $contentType)) {
            throw new OpenAPIException('Invalid response body content type.', 400);
        }
        $data = $httpResponse->content();
        if (str_starts_with($contentType, 'application/') && str_ends_with($contentType, 'json')) {
            $data = json_decode($data);
        }

        /** @var MediaType $mediaType */
        $mediaType = $openAPIResponse->content->{$contentType};
        $this->validateMediaTypeResponse($mediaType, $data);
    }

    public function validateSecurityScheme(SecurityScheme $securityScheme, HttpRequestInterface $httpRequest): void
    {
        if ($securityScheme->type === SecuritySchemeType::API_KEY) {
            $this->validateSecuritySchemeApiKeyRequest($securityScheme, $httpRequest);
        } elseif ($securityScheme->type === SecuritySchemeType::HTTP) {
            $this->validateSecuritySchemeHttpRequest($securityScheme, $httpRequest);
        } elseif ($securityScheme->type === SecuritySchemeType::OAUTH2) {
            $this->validateSecuritySchemeOAuth2Request($securityScheme, $httpRequest);
        } elseif ($securityScheme->type === SecuritySchemeType::OPEN_ID_CONNECT) {
            $this->validateSecuritySchemeOpenIdConnectRequest($securityScheme, $httpRequest);
        }
    }

    private function validateSecuritySchemeApiKeyRequest(
        SecurityScheme $securityScheme,
        HttpRequestInterface $httpRequest
    ): void {
        $token = match ($securityScheme->in) {
            SecuritySchemeIn::QUERY => $httpRequest->get($securityScheme->name) ?? null,
            SecuritySchemeIn::HEADER => $httpRequest->header($securityScheme->name)[0] ?? null,
            SecuritySchemeIn::COOKIE => $httpRequest->cookie($securityScheme->name) ?? null,
            default => null,
        };
        if ($token === null) {
            throw new OpenAPIException(
                'Missing apiKey in ' . $securityScheme->in->value . '[' . $securityScheme->name . ']'
            );
        }
    }

    private function validateSecuritySchemeHttpRequest(
        SecurityScheme $securityScheme,
        HttpRequestInterface $httpRequest
    ): void {
        if ($securityScheme->scheme === 'bearer') {
            $value = $httpRequest->header('authorization')[0] ?? '';
            if (!str_starts_with(strtolower($value), 'bearer ')) {
                throw new OpenAPIException('Invalid Authorization. Required bearer authorization.');
            }
            [, $value] = explode(' ', $value, 2);
            if (!strlen($value)) {
                throw new OpenAPIException('Invalid Authorization. Missing token in bearer authorization.');
            }
            if ($securityScheme->bearerFormat === 'jwt') {
                if (substr_count($value, '.') !== 2) {
                    throw new OpenAPIException('Invalid Authorization. Misinformed jw-token. (1)');
                }
                $jwt = explode('.', $value);
                $header = base64_decode($jwt[0]);
                $content = base64_decode($jwt[1]);
                if (!$header || !$content) {
                    throw new OpenAPIException('Invalid Authorization. Misinformed jw-token. (2)');
                }
                $header = json_decode($header, true);
                $content = json_decode($content, true);
                if (!is_array($header) || !is_array($content)) {
                    throw new OpenAPIException('Invalid Authorization. Misinformed jw-token. (3)');
                }
                if (!count($header) || !count($content)) {
                    throw new OpenAPIException('Invalid Authorization. Misinformed jw-token. (4)');
                }
            }
        } elseif ($securityScheme->scheme === 'basic') {
            $value = $httpRequest->header('authorization')[0] ?? '';
            if (!str_starts_with(strtolower($value), 'basic ')) {
                throw new OpenAPIException('Invalid Authorization. Required basic authorization.');
            }
            [, $value] = explode(' ', $value, 2);
            if (!strlen($value)) {
                throw new OpenAPIException('Invalid Authorization. Missing token in bearer authorization.');
            }
            $value = base64_decode($value);
            if ($value === false || !str_contains($value, ':')) {
                throw new OpenAPIException('Invalid Authorization. Missing username-password-tuppel.');
            }
        }
    }

    private function validateSecuritySchemeOAuth2Request(
        SecurityScheme $securityScheme,
        HttpRequestInterface $httpRequest
    ): void {
        if ($securityScheme->flows->implicit) {
            // @TODO
        } elseif ($securityScheme->flows->password) {
            // @TODO
        } elseif ($securityScheme->flows->clientCredentials) {
            // @TODO
        } elseif ($securityScheme->flows->authorizationCode) {
            // @TODO
        }
    }

    private function validateSecuritySchemeOpenIdConnectRequest(
        SecurityScheme $securityScheme,
        HttpRequestInterface $httpRequest
    ): void {
        // @TODO
    }

    private function validateServer(Server $server, HttpRequestInterface $httpRequest): bool
    {
        $regex = preg_replace_callback('(\{([^}]+)})', function (array $group) {
            return '(?P<' . $group[1] . '>[^\/]+)';
        }, $server->url);
        $regex = '(^' . $regex . '(?P<__openapi_realpath>.*))';
        if (!preg_match($regex, $httpRequest->url(), $matches)) {
            return false;
        }
        if (!is_null($server->variables)) {
            foreach ($matches as $key => $value) {
                if ($key === '__openapi_realpath' || is_numeric($key)) {
                    continue;
                }
                if (!property_exists($server->variables, $key)) {
                    continue;
                }
                try {
                    $this->validateServerVariable($server->variables->{$key}, $value);
                    $parameters = $httpRequest->attribute('__openapi_parameters') ?? [];
                    $parameters[$key] = $value;
                    $httpRequest->attribute('__openapi_parameters', $parameters);
                } catch (OpenAPIException $e) {
                    return false;
                }
            }
        }

        $httpRequest->attribute('__openapi_realpath', $httpRequest->url()->path());
        if (array_key_exists('__openapi_realpath', $matches)) {
            $httpRequest->attribute('__openapi_realpath', $matches['__openapi_realpath']);
            unset($matches['__openapi_realpath']);
        }
        $httpRequest->attribute('__openapi_server', $server);
        return true;
    }

    private function validateServerVariable(ServerVariable $serverVariable, mixed $value): void
    {
        if (!in_array($value, $serverVariable->enum, true)) {
            throw new OpenAPIException('Invalid Server variable value.', 400);
        }
    }

    private function validateSchema(AbstractType|Reference $type, mixed $value): void
    {
        while ($type instanceof Reference) {
            $type = $type->getReference();
        }
        switch ($type->type) {
            case 'allOf':
                $this->validateAllOfType($type, $value);
                break;
            case 'anyOf':
                $this->validateAnyOfType($type, $value);
                break;
            case 'oneOf':
                $this->validateOneOfType($type, $value);
                break;
            case 'boolean':
                $this->validateBooleanType($type, $value);
                break;
            case 'array':
                $this->validateArrayType($type, $value);
                break;
            case 'integer':
                $this->validateIntegerType($type, $value);
                break;
            case 'number':
                $this->validateNumberType($type, $value);
                break;
            case 'string':
                $this->validateStringType($type, $value);
                break;
            case 'null':
                $this->validateNullType($type, $value);
                break;
            case 'object':
                $this->validateObjectType($type, $value);
                break;
        };
    }

    private function validateAllOfType(AllOfType $type, mixed $value): void
    {
        // @TODO discriminator
        foreach ($type->types as $subType) {
            $this->validateSchema($subType, $value);
        }
    }

    private function validateAnyOfType(AnyOfType $type, mixed $value): void
    {
        foreach ($type->types as $subType) {
            try {
                $this->validateSchema($subType, $value);
                return;
            } catch (OpenAPIException $e) {
                // ignore
            }
        }
        throw new OpenAPIException('Invalid anyOf value.', 400);
    }

    private function validateOneOfType(OneOfType $type, mixed $value): void
    {
        // @TODO discriminator
        $count = 0;
        foreach ($type->types as $subType) {
            try {
                $this->validateSchema($subType, $value);
                $count++;
            } catch (OpenAPIException $e) {
                // ignored
            }
        }
        if ($count !== 1) {
            throw new OpenAPIException('Invalid oneOf value.', 400);
        }
    }

    private function validateBooleanType(BoolType $type, mixed $value): void
    {
        if ($type->nullable && $value === null) {
            return;
        }
        if (!is_bool($value)) {
            throw new OpenAPIException('Invalid boolean value.', 400);
        }
    }

    private function validateArrayType(ArrayType $type, mixed $value): void
    {
        if ($type->nullable && $value === null) {
            return;
        }
        if (!is_array($value)) {
            throw new OpenAPIException('Invalid array value.', 400);
        }
        if ($type->minItems !== null || $type->maxItems !== null || $type->uniqueItems) {
            $count = count($value);
            if ($type->minItems !== null && $count < $type->minItems) {
                throw new OpenAPIException('Invalid array min count. Required ' . $type->minItems . ' items.', 400);
            }
            if ($type->maxItems !== null && $count > $type->maxItems) {
                throw new OpenAPIException('Invalid array max count. Required ' . $type->maxItems . ' items.', 400);
            }
            if ($type->uniqueItems && $count !== count(array_unique($value))) {
                throw new OpenAPIException('Invalid array. Array items are not unique.', 400);
            }
        }
        if (!$type->items) {
            return;
        }
        foreach ($value as $subValue) {
            $this->validateSchema($type->items, $subValue);
        }
    }

    private function validateIntegerType(IntegerType $type, mixed $value): void
    {
        if ($type->nullable && $value === null) {
            return;
        }
        if ($value === null) {
            throw new OpenAPIException('Invalid integer value.', 400);
        }
        if ($value != (int)$value) {
            throw new OpenAPIException('Invalid integer value.', 400);
        }
        $value = (int)$value;
        if (!is_null($type->minimum) && $value < $type->minimum) {
            throw new OpenAPIException(
                'Invalid integer, value is to low (inclusive minimum: .' . $type->minimum . ').',
                400
            );
        }
        if (!is_null($type->exclusiveMinimum) && $value <= $type->exclusiveMinimum) {
            throw new OpenAPIException(
                'Invalid integer, value is to low (exclusive minimum: .' . $type->exclusiveMinimum . ').', 400
            );
        }
        if (!is_null($type->exclusiveMaximum) && $value >= $type->exclusiveMaximum) {
            throw new OpenAPIException(
                'Invalid integer, value is to high (exclusive maximum: .' . $type->exclusiveMaximum . ').', 400
            );
        }
        if (!is_null($type->maximum) && $value > $type->maximum) {
            throw new OpenAPIException(
                'Invalid integer, value is to high (inclusive maximum: .' . $type->maximum . ').', 400
            );
        }
    }

    private function validateNumberType(NumberType $type, mixed $value): void
    {
        if ($type->nullable && $value === null) {
            return;
        }
        if (!is_numeric($value)) {
            throw new OpenAPIException('Invalid number value.', 400);
        }
        if (!is_null($type->minimum) && $value < $type->minimum) {
            throw new OpenAPIException(
                'Invalid number, value is to low (inclusive minimum: .' . $type->minimum . ').',
                400
            );
        }
        if (!is_null($type->exclusiveMinimum) && $value <= $type->exclusiveMinimum) {
            throw new OpenAPIException(
                'Invalid number, value is to low (exclusive minimum: .' . $type->exclusiveMinimum . ').', 400
            );
        }
        if (!is_null($type->exclusiveMaximum) && $value >= $type->exclusiveMaximum) {
            throw new OpenAPIException(
                'Invalid number, value is to high (exclusive maximum: .' . $type->exclusiveMaximum . ').', 400
            );
        }
        if (!is_null($type->maximum) && $value > $type->maximum) {
            throw new OpenAPIException(
                'Invalid number, value is to high (inclusive maximum: .' . $type->maximum . ').', 400
            );
        }
    }

    private function validateStringType(StringType $type, mixed $value): void
    {
        if ($type->nullable && $value === null) {
            return;
        }
        if (!is_string($value)) {
            throw new OpenAPIException('Invalid string.', 400);
        }
        if (is_array($type->enum) && !in_array($value, $type->enum, true)) {
            throw new OpenAPIException('Invalid enum. Valid: ' . implode(',', $type->enum), 400);
        }
        if ($type->minLength !== null || $type->maxLength !== null) {
            $length = strlen($value);
            if ($type->minLength !== null && $length < $type->minLength) {
                throw new OpenAPIException('Invalid string (minimal length: ' . $type->minLength . ').', 400);
            }
            if ($type->maxLength !== null && $length > $type->maxLength) {
                throw new OpenAPIException('Invalid string (maximal length: ' . $type->minLength . ').', 400);
            }
        }
        if ($type->format) {
            $filterValidateName = 'FILTER_VALIDATE_' . strtoupper($type->format);
            if (defined($filterValidateName)) {
                if (!filter_var($value, constant($filterValidateName))) {
                    throw new OpenAPIException('Invalid ' . $type->format . '.', 400);
                }
            } elseif ($type->format === 'hex') {
                if (!preg_match('/^[0-9a-f]+$/i', $value)) {
                    throw new OpenAPIException('Invalid ' . $type->format . '.', 400);
                }
            } elseif ($type->format === 'jwt') {
                if (substr_count($value, '.') !== 2) {
                    throw new OpenAPIException('Invalid ' . $type->format . ' (1).', 400);
                }
                $jwt = explode('.', $value);
                $header = base64_decode($jwt[0]);
                $content = base64_decode($jwt[1]);
                if (!$header || !$content) {
                    throw new OpenAPIException('Invalid ' . $type->format . ' (2).', 400);
                }
                $header = json_decode($header, true);
                $content = json_decode($content, true);
                if (!is_array($header) || !is_array($content)) {
                    throw new OpenAPIException('Invalid ' . $type->format . ' (3).', 400);
                }
                if (!count($header) || !count($content)) {
                    throw new OpenAPIException('Invalid ' . $type->format . ' (4).', 400);
                }
            } elseif ($type->format === 'uuid') {
                if (strlen($value) !== 36) {
                    throw new OpenAPIException('Invalid ' . $type->format . ' (1).', 400);
                }
                if (!preg_match(
                    '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
                    $value
                )) {
                    throw new OpenAPIException('Invalid ' . $type->format . ' (2).', 400);
                }
            } elseif ($type->format === 'date-time' && !preg_match(
                    '/^(\d{4}-\d{2}-\d{2})(T|\s)(\d{2}:\d{2}:\d{2})(\.\d+)?([+-]\d{2}:?\d{2})?$/',
                    $value
                )) {
                throw new OpenAPIException('Invalid ' . $type->format . '.', 400);
            } elseif ($type->format === 'date' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                throw new OpenAPIException('Invalid ' . $type->format . '.', 400);
            } elseif ($type->format === 'time' && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
                throw new OpenAPIException('Invalid ' . $type->format . '.', 400);
            } elseif ($type->format === 'password') {
                // no explicit check
            }
        }
        if ($type->pattern && !preg_match($type->pattern, $value)) {
            throw new OpenAPIException('Invalid string pattern.', 400);
        }
    }

    private function validateNullType(NullType $type, mixed $value): void
    {
        if ($value !== null) {
            throw new OpenAPIException('Invalid null value.', 400);
        }
    }

    private function validateObjectType(ObjectType $type, mixed $value): void
    {
        if ($type->nullable && $value === null) {
            return;
        }
        if (is_array($value)) {
            $value = (object)$value;
        }
        if (!is_object($value)) {
            throw new OpenAPIException('Invalid object.');
        }
        $additionalProperties = clone $value;
        if ($type->properties) {
            foreach ($type->properties as $key => $scheme) {
                if (property_exists($additionalProperties, $key)) {
                    $this->validateSchema($scheme, $additionalProperties->{$key});
                    unset($additionalProperties->{$key});
                }
            }
        }
        if ($type->additionalProperties === false) {
            if (count(get_object_vars($additionalProperties))) {
                throw new OpenAPIException('No additional properties are allowed.');
            }
        } elseif ($type->additionalProperties instanceof AbstractType) {
            foreach ($additionalProperties as $subValue) {
                $this->validateSchema($type->additionalProperties, $subValue);
            }
        }
        if ($type->required) {
            foreach ($type->required as $key) {
                if (!property_exists($value, $key)) {
                    throw new OpenAPIException('Missing required property ' . $key . '.');
                }
            }
        }
        if ($type->minProperties !== null || $type->maxProperties !== null) {
            $count = count(get_object_vars($value));
            if ($type->minProperties !== null && $count < $type->minProperties) {
                throw new OpenAPIException(
                    'Invalid property count. Required minimal ' . $type->minProperties . ' properties.'
                );
            }
            if ($type->maxProperties !== null && $count > $type->maxProperties) {
                throw new OpenAPIException(
                    'Invalid property count. Required maximal ' . $type->maxProperties . ' properties.'
                );
            }
        }
        // @TODO validate discriminator
    }

}
