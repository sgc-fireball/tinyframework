<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\OpenAPIValidator;
use TinyFramework\OpenAPI\Types\NullType;

class NullTypeTest extends TestCase
{

    private OpenAPIValidator $openAPIValidator;
    private ReflectionMethod $reflectionMethod;

    public function setUp(): void
    {
        $this->openAPIValidator = new OpenAPIValidator(new OpenAPI());
        $reflectionClass = new ReflectionClass($this->openAPIValidator);
        $this->reflectionMethod = $reflectionClass->getMethod('validateNullType');
        $this->reflectionMethod->setAccessible(true);
    }

    public function testNull(): void
    {
        $scheme = NullType::parse([
            'description' => 'description',
        ]);
        $this->assertInstanceOf(NullType::class, $scheme);
        $this->assertTrue($scheme->nullable);
        $this->assertEquals('description', $scheme->description);
        $this->assertNull($scheme->default);
        $this->assertNull($scheme->example);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, false);
    }

}
