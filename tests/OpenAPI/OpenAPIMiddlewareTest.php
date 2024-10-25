<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use TinyFramework\Http\Middleware\OpenAPIMiddleware;
use TinyFramework\Http\RequestInterface as HttpRequestInterface;
use TinyFramework\Http\Request as HttpRequest;
use TinyFramework\Http\Response as HttpResponse;
use TinyFramework\Http\URL;
use TinyFramework\Tests\Feature\FeatureTestCase;

class OpenAPIMiddlewareTest extends FeatureTestCase
{

    public function testValidRequest(): void
    {
        $middleware = new OpenAPIMiddleware();
        $request = HttpRequest::factory(
            'GET',
            URL::factory('http://localhost:8000/api/pets'),
            ['tags' => ['test'], 'limit' => 12]
        );

        $next = function (HttpRequestInterface $request): HttpResponse {
            return HttpResponse::json([
                ['id' => 2, 'name' => 'Animal 2'],
                ['id' => 3, 'name' => 'Animal 3', 'tag' => 'zoo'],
            ]);
        };

        $response = $middleware->handle($request, $next, 'tests/assets/openapi.yaml');
        $this->assertEquals(200, $response->code());
        $content = $response->content();
        $this->assertIsString($content);
        $content = json_decode($content, true);
        $this->assertIsArray($content);
        $this->assertArrayNotHasKey('error', $content);
        $this->assertCount(2, $content);
    }

    public function testInvalidRequestQueryParameter(): void
    {
        $middleware = new OpenAPIMiddleware();
        $request = HttpRequest::factory(
            'GET',
            URL::factory('http://localhost:8000/api/pets'),
            ['tags' => [512], 'limit' => 'a']
        );

        $next = function (HttpRequestInterface $request): HttpResponse {
            return HttpResponse::json([
                ['id' => 2, 'name' => 'Animal 2'],
                ['id' => 3, 'name' => 'Animal 3', 'tag' => 'zoo'],
            ]);
        };

        $response = $middleware->handle($request, $next, 'tests/assets/openapi.yaml');
        $this->assertTrue($response->code() >= 400, 'Status Code must be greater or equal 400.');
        $this->assertTrue($response->code() <= 499, 'Status Code must be lower or equal then 499.');
        $content = $response->content();
        $this->assertIsString($content);
        $content = json_decode($content, true);
        $this->assertIsArray($content);
        $this->assertCount(2, $content);
        $this->assertArrayHasKey('error', $content);
        $this->assertIsInt($content['error']);
        $this->assertGreaterThan(0, $content['error']);
        $this->assertArrayHasKey('message', $content);
        $this->assertIsString($content['message']);
    }

    public function testInvalidRequestPathParameter(): void
    {
        $middleware = new OpenAPIMiddleware();
        $request = HttpRequest::factory(
            'GET',
            URL::factory('http://localhost:8000/api/pets/abc'),
        );

        $next = function (HttpRequestInterface $request): HttpResponse {
            return HttpResponse::json();
        };

        $response = $middleware->handle($request, $next, 'tests/assets/openapi.yaml');
        $this->assertTrue($response->code() >= 400, 'Status Code must be greater or equal 400.');
        $this->assertTrue($response->code() <= 499, 'Status Code must be lower or equal then 499.');
    }

    public function testInvalidResponse(): void
    {
        $middleware = new OpenAPIMiddleware();
        $request = HttpRequest::factory(
            'GET',
            URL::factory('http://localhost:8000/api/pets/512')
        );

        $next = function (HttpRequestInterface $request): HttpResponse {
            return HttpResponse::json([
                'error' => 0,
                'data' => [
                    'id' => 3,
                    'name' => 'Animal 3',
                    'tag' => 'zoo',
                ],
            ]);
        };

        $response = $middleware->handle($request, $next, 'tests/assets/openapi.yaml');
        $this->assertTrue($response->code() >= 500, 'Status Code must be greater or equal 500.');
        $this->assertTrue($response->code() <= 599, 'Status Code must be lower or equal then 599.');
        $content = $response->content();
        $this->assertIsString($content);
        $content = json_decode($content, true);
        $this->assertIsArray($content);
        $this->assertCount(2, $content);
        $this->assertArrayHasKey('error', $content);
        $this->assertIsInt($content['error']);
        $this->assertGreaterThan(0, $content['error']);
        $this->assertArrayHasKey('message', $content);
        $this->assertIsString($content['message']);
    }

}
