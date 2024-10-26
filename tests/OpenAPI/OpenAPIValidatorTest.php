<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\Http\Request;
use TinyFramework\Http\Response;
use TinyFramework\Http\URL;
use TinyFramework\OpenAPI\OpenAPIValidator;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\Objects\Operation;
use TinyFramework\OpenAPI\Objects\Server;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\WebToken\JWT;

class OpenAPIValidatorTest extends TestCase
{

    public function testRequest(): void
    {
        $openAPI = OpenAPI::parseYamlFile('tests/assets/openapi.yaml');

        $request = Request::factory(
            'GET',
            URL::factory('http://localhost:8000/api/pets'),
            ['tags' => ['zoo'], 'limit' => 12]
        );
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);

        $server = $request->attribute('__openapi_server');
        $this->assertInstanceOf(Server::class, $server);
        $this->assertEquals('http://localhost:8000/api', $server->url);

        $operation = $request->attribute('__openapi_operation');
        $this->assertInstanceOf(Operation::class, $operation);
        $this->assertEquals('findPets', $operation->operationId);

        $response = Response::json([
            ['id' => 2, 'name' => 'Animal 2'],
            ['id' => 3, 'name' => 'Animal 3', 'tag' => 'zoo'],
        ]);

        $openAPIValidator->validateHttpResponse($request, $response);
        $this->assertEquals(200, $response->code());
        $content = $response->content();
        $this->assertIsString($content);
        $content = json_decode($content, true);
        $this->assertIsArray($content);
    }

    public function testRequestValidServerPrefix(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

servers:
    - url: "http://localhost:8000/api/v1"

paths:
    /login:
        get: []
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost:8000/api/v1/login')
        );
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
        $this->assertTrue(true);
    }

    public function testRequestValidServerInvalidPrefix(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

servers:
    - url: "http://localhost:8000/api/v1"

paths:
    /login:
        get: []
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost:8000/api/v2/login')
        );
        $this->expectException(OpenAPIException::class);
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
    }

    public function testRequestValidServerProtocol(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

servers:
    -
        url: "{schema}://localhost:8000/api/v1"
        variables:
            schema:
                default: http
                enum:
                    - http
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost:8000/api/v1')
        );
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
        $this->assertTrue(true);
    }

    public function testRequestValidServerInvalidProtocol(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

servers:
    -
        url: "{schema}://localhost:8000/api/v1"
        variables:
            schema:
                default: https
                enum:
                    - https
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost:8000/api/v1')
        );
        $this->expectException(OpenAPIException::class);
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
    }

    public function testRequestInvalidPath(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /logout:
        get: []
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost/login')
        );
        $this->expectException(OpenAPIException::class);
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
    }

    public function testRequestInvalidMethod(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /logout:
        post: []
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost/logout')
        );
        $this->expectException(OpenAPIException::class);
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
    }

    public function testRequestBody(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        post:
            description: "Login API Endpoint"
            content:
                application/json:
                    schema:
                        type: object
                        properties:
                            email:
                                type: "string"
                                format: "email"
                            password:
                                type: "string"
                                format: "password"
                                minLength: 8
EOF
        );
        $request = Request::factory(
            'POST',
            URL::factory('http://localhost:8000/login'),
            [],
            ['email' => 'user@example.de', 'password' => $password = random_bytes(16)],
            ['Content-Type' => ['application/json']]
        );
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
        $this->assertEquals('user@example.de', $request->post('email'));
        $this->assertEquals($password, $request->post('password'));
    }

    public function testRequestBodyInvalid(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        post:
            description: "Login API Endpoint"
            requestBody:
                required: true
                content:
                    application/json:
                        schema:
                            type: object
                            additionalProperties: false
                            required:
                                - email
                                - password
                            properties:
                                email:
                                    type: "string"
                                    format: "email"
                                password:
                                    type: "string"
                                    format: "password"
                                    minLength: 8
EOF
        );
        $request = Request::factory(
            'POST',
            URL::factory('http://localhost:8000/login'),
            [],
            ['email' => 'user@example.de'],
            ['Content-Length' => 1, 'Content-Type' => ['application/json']]
        );
        $this->expectException(OpenAPIException::class);
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
    }

    public function testResponseBody(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        post:
            description: "Login API Endpoint"
            responses:
                "200":
                    description: "Successful Login"
                    content:
                        application/json:
                          schema:
                            type: object
                            additionalProperties: false
                            required:
                                - token
                            properties:
                                token:
                                    type: "string"
                                    format: "jwt"
EOF
        );
        $request = Request::factory(
            'POST',
            URL::factory('http://localhost:8000/login')
        );
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);

        $jwt = new JWT(JWT::ALG_HS256, random_bytes(32));
        $response = Response::json(['token' => $jwt->encode()]);
        $openAPIValidator->validateHttpResponse($request, $response);
        $this->assertStringStartsWith('ey', json_decode($response->content(), true)['token']);
    }

    public function testResponseBodyInvalid(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        post:
            description: "Login API Endpoint"
            responses:
                "200":
                    description: "Successful Login"
                    content:
                        application/json:
                          schema:
                            type: object
                            additionalProperties: false
                            required:
                                - token
                            properties:
                                token:
                                    type: "string"
                                    format: "jwt"
EOF
        );
        $request = Request::factory(
            'POST',
            URL::factory('http://localhost:8000/login')
        );
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);

        $this->expectException(OpenAPIException::class);
        $response = Response::json(['token' => null]);
        $openAPIValidator->validateHttpResponse($request, $response);
    }

    public function testSecuritySchemeApiKeyQuery(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        get:
            security:
                - login: []
components:
    securitySchemes:
        login:
            type: "apiKey"
            in: "query"
            name: "token"
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost/login'),
            ['token' => 'token']
        );
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
        $this->assertEquals('token', $request->get('token'));
    }

    public function testSecuritySchemeApiKeyQueryInvalid(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        get:
            security:
                - login: []
components:
    securitySchemes:
        login:
            type: "apiKey"
            in: "query"
            name: "token"
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost/login'),
        );
        $this->expectException(OpenAPIException::class);
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
    }

    public function testSecuritySchemeApiKeyHeader(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        get:
            security:
                - login: []
components:
    securitySchemes:
        login:
            type: "apiKey"
            in: "header"
            name: "x-token"
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost/login'),
            headers: ['x_token' => ['token']]
        );
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
        $this->assertEquals('token', $request->header('x-token')[0]);
    }

    public function testSecuritySchemeApiKeyHeaderInvalid(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        get:
            security:
                - login: []
components:
    securitySchemes:
        login:
            type: "apiKey"
            in: "header"
            name: "x-token"
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost/login')
        );
        $this->expectException(OpenAPIException::class);
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
    }

    public function testSecuritySchemeApiKeyCookie(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        get:
            security:
                - login: []
components:
    securitySchemes:
        login:
            type: "apiKey"
            in: "cookie"
            name: "ctoken"
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost/login'),
            cookies: ['ctoken' => 'token']
        );
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
        $this->assertEquals('token', $request->cookie('ctoken'));
    }

    public function testSecuritySchemeApiKeyCookieInvalid(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        get:
            security:
                - login: []
components:
    securitySchemes:
        login:
            type: "apiKey"
            in: "cookie"
            name: "ctoken"
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost/login')
        );
        $this->expectException(OpenAPIException::class);
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
    }

    public function testSecuritySchemeHttpBasic(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        get:
            security:
                - login: []
components:
    securitySchemes:
        login:
            type: "http"
            scheme: "basic"
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost/login'),
            headers: ['authorization' => ['Basic ' . base64_encode('user:pass')]]
        );
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
        $this->assertTrue(true);
    }

    public function testSecuritySchemeHttpBasicInvalid(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        get:
            security:
                - login: []
components:
    securitySchemes:
        login:
            type: "http"
            scheme: "basic"
EOF
        );
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost/login'),
            headers: ['authorization' => ['Basic invalid']]
        );
        $this->expectException(OpenAPIException::class);
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
    }

    public function testSecuritySchemeHttpBearer(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        get:
            security:
                - login: []
components:
    securitySchemes:
        login:
            type: "http"
            scheme: "bearer"
            bearerFormat: "jwt"
EOF
        );
        $jwt = base64_encode(json_encode(['typ' => 'jwt']));
        $jwt .= '.';
        $jwt .= base64_encode(json_encode(['scope' => ['login']]));
        $jwt .= '.';
        $jwt .= base64_encode('nosign');
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost/login'),
            headers: ['authorization' => ['Bearer ' . $jwt]]
        );
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
        $this->assertTrue(true);
    }

    public function testSecuritySchemeHttpBearerInvalid(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0

paths:
    /login:
        get:
            security:
                - login: []
components:
    securitySchemes:
        login:
            type: "http"
            scheme: "bearer"
            bearerFormat: "jwt"
EOF
        );
        $jwt = base64_encode(json_encode(['typ' => 'jwt']));
        $jwt .= '.';
        $jwt .= base64_encode(json_encode(['scope' => ['login']]));
        $request = Request::factory(
            'GET',
            URL::factory('http://localhost/login'),
            headers: ['authorization' => ['Bearer ' . $jwt]]
        );
        $this->expectException(OpenAPIException::class);
        $openAPIValidator = new OpenAPIValidator($openAPI);
        $openAPIValidator->validateHttpRequest($request);
    }

    // @TODO oauth implicit
    // @TODO oauth password
    // @TODO oauth clientCredentials
    // @TODO oauth authorizationCode

    // @TODO openIdConnect

}
