<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\OpenAPIValidator;
use TinyFramework\OpenAPI\Types\BoolType;

class BoolTypeTest extends TestCase
{

    private OpenAPIValidator $openAPIValidator;
    private ReflectionMethod $reflectionMethod;

    public function setUp(): void
    {
        $this->openAPIValidator = new OpenAPIValidator(new OpenAPI());
        $reflectionClass = new ReflectionClass($this->openAPIValidator);
        $this->reflectionMethod = $reflectionClass->getMethod('validateBooleanType');
        $this->reflectionMethod->setAccessible(true);
    }

    public function testNormal(): void
    {
        $scheme = BoolType::parse([
            'description' => 'description',
            'default' => false,
            'example' => false,
        ]);
        $this->assertInstanceOf(BoolType::class, $scheme);
        $this->assertEquals(false, $scheme->nullable);
        $this->assertEquals('description', $scheme->description);
        $this->assertEquals(false, $scheme->default);
        $this->assertEquals(false, $scheme->example);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, true);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, false);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
    }

    public function testNullable(): void
    {
        $scheme = BoolType::parse([
            'nullable' => true,
            'description' => 'description',
            'default' => false,
            'example' => false,
        ]);
        $this->assertInstanceOf(BoolType::class, $scheme);
        $this->assertEquals(true, $scheme->nullable);
        $this->assertEquals('description', $scheme->description);
        $this->assertEquals(false, $scheme->default);
        $this->assertEquals(false, $scheme->example);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, true);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, false);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'abc');
    }

}
