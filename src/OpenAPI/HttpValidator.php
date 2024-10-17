<?php

namespace TinyFramework\OpenAPI;

use TinyFramework\Http\RequestInterface as HttpRequestInterface;
use TinyFramework\Http\Response as HttpResponse;
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

class HttpValidator
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
                if (!preg_match($regex, $realPath)) {
                    continue;
                }
                if (!isset($pathItem->{$method})) {
                    throw new OpenAPIException('Method not allowed (1)', 405);
                }
                if (is_null($pathItem->{$method})) {
                    throw new OpenAPIException('Method not allowed (2)', 405);
                }
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
        $mediaType->schema?->validate($httpRequest->post());
    }

    private function validateMediaTypeResponse(MediaType $mediaType, mixed $value): void
    {
        $mediaType->schema?->validate($value);
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
        // @TODO
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
        if ($securityScheme->type === 'apiKey') {
            $this->validateSecuritySchemeApiKeyRequest($securityScheme, $httpRequest);
        } elseif ($securityScheme->type === 'http') {
            $this->validateSecuritySchemeHttpRequest($securityScheme, $httpRequest);
        } elseif ($securityScheme->type === 'oauth2') {
            $this->validateSecuritySchemeOAuth2Request($securityScheme, $httpRequest);
        } elseif ($securityScheme->type === 'openIdConnect') {
            $this->validateSecuritySchemeOpenIdConnectRequest($securityScheme, $httpRequest);
        }
    }

    private function validateSecuritySchemeApiKeyRequest(
        SecurityScheme $securityScheme,
        HttpRequestInterface $httpRequest
    ): void {
        $token = match ($securityScheme->in) {
            'query' => $httpRequest->get($securityScheme->name) ?? '',
            'header' => $httpRequest->header($securityScheme->name)[0] ?? '',
            'cookie' => $httpRequest->cookie($securityScheme->name) ?? ''
        };
        if (!strlen($token)) {
            throw new OpenAPIException('Missing apiKey in ' . $securityScheme->in . '[' . $securityScheme->name . ']');
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

}
