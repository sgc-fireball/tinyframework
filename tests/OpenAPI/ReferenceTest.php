<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\Http\Request;
use TinyFramework\Http\Response;
use TinyFramework\Http\URL;
use TinyFramework\OpenAPI\HttpValidator;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\WebToken\JWT;

class ReferenceTest extends TestCase
{

    public function testReferences(): void
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
  /register:
    \$ref: '#/components/pathItems/Register'

components:
  securitySchemes:
    login:
      type: "apiKey"
      in: "query"
      name: "token"

  pathItems:
    Register:
      post:
        security:
          - login: []
        parameters:
          - \$ref: '#/components/parameters/Token'
        requestBody:
          \$ref: '#/components/requestBodies/Register'
        responses:
          200:
            \$ref: '#/components/responses/Register200'

  parameters:
    Token:
      name: token
      in: query
      description: "API Token"
      required: true
      schema:
        \$ref: '#/components/schemas/Token'

  requestBodies:
    Register:
      content:
        application/json:
          schema:
            \$ref: '#/components/schemas/Customer'

  responses:
    Register200:
      description: successful register response.
      content:
        application/json:
          schema:
            \$ref: '#/components/schemas/TokenResponse'

  schemas:
    Token:
      type: string
      format: jwt
    TokenResponse:
      type: object
      additionalProperties: false
      required:
        - token
      properties:
        token:
          \$ref: '#/components/schemas/Token'
    Name:
      type: string
      minLength: 2
      maxLength: 60
    Age:
      type: integer
      minimum: 0
      maximum: 150
    Email:
      type: string
      format: email
      minLength: 7
      maxLength: 255
    Password:
      type: string
      minLength: 8
    Customer:
      type: object
      additionalProperties: false
      required:
        - name
        - email
        - password
        - age
      properties:
        name:
          \$ref: '#/components/schemas/Name'
        email:
          \$ref: '#/components/schemas/Email'
        password:
          \$ref: '#/components/schemas/Password'
        age:
          \$ref: '#/components/schemas/Age'
EOF
        );
        $request = Request::factory(
            'POST',
            URL::factory('http://localhost:8000/api/v1/register'),
            ['token' => 'secret'],
            [
                'name' => 'name',
                'email' => '123user@example.com',
                'password' => random_bytes(16),
                'age' => mt_rand(18, 100),
            ],
            ['Content-Type' => 'application/json']
        );
        $this->assertEquals('POST', $request->method());
        $this->assertEquals('/api/v1/register', $request->url()->path());
        $httpValidator = new HttpValidator($openAPI);
        $httpValidator->validateHttpRequest($request);

        $jwt = new JWT(JWT::ALG_HS256, random_bytes(32));
        $response = Response::json(['token' => $jwt->encode()]);
        $httpValidator->validateHttpResponse($request, $response);

        $this->assertTrue(true);
    }

}
