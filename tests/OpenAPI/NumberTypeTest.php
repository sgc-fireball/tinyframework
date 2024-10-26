<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\OpenAPIValidator;
use TinyFramework\OpenAPI\Types\NumberType;

class NumberTypeTest extends TestCase
{

    private OpenAPIValidator $openAPIValidator;
    private ReflectionMethod $reflectionMethod;

    public function setUp(): void
    {
        $this->openAPIValidator = new OpenAPIValidator(new OpenAPI());
        $reflectionClass = new ReflectionClass($this->openAPIValidator);
        $this->reflectionMethod = $reflectionClass->getMethod('validateNumberType');
        $this->reflectionMethod->setAccessible(true);
    }

    public function testNormal(): void
    {
        $scheme = NumberType::parse([]);
        $this->assertInstanceOf(NumberType::class, $scheme);
        $this->assertEquals('number', $scheme->type);
        $this->assertEquals(false, $scheme->nullable);
        $this->assertEquals(null, $scheme->format);
        $this->assertEquals(null, $scheme->default);
        $this->assertEquals(null, $scheme->example);
        $this->assertEquals(null, $scheme->minimum);
        $this->assertEquals(null, $scheme->exclusiveMinimum);
        $this->assertEquals(null, $scheme->exclusiveMaximum);
        $this->assertEquals(null, $scheme->maximum);
        $this->assertEquals(null, $scheme->xml);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, -2.3);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, -1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2.3);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
    }

    public function testNullable(): void
    {
        $scheme = NumberType::parse(['nullable' => true]);
        $this->assertInstanceOf(NumberType::class, $scheme);
        $this->assertEquals(true, $scheme->nullable);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, -2.3);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, -1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2.3);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'a');
    }

    public function testMinimum(): void
    {
        $scheme = NumberType::parse(['minimum' => 0]);
        $this->assertInstanceOf(NumberType::class, $scheme);
        $this->assertEquals(0, $scheme->minimum);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2.3);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, -0.1);
    }

    public function testExclusiveMinimum(): void
    {
        $scheme = NumberType::parse(['exclusiveMinimum' => 0]);
        $this->assertInstanceOf(NumberType::class, $scheme);
        $this->assertEquals(0, $scheme->exclusiveMinimum);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2.3);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0.000001);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0);
    }

    public function testExclusiveMaximum(): void
    {
        $scheme = NumberType::parse(['exclusiveMaximum' => 2]);
        $this->assertInstanceOf(NumberType::class, $scheme);
        $this->assertEquals(2, $scheme->exclusiveMaximum);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1.999999);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2);
    }

    public function testMaximum(): void
    {
        $scheme = NumberType::parse(['maximum' => 2]);
        $this->assertInstanceOf(NumberType::class, $scheme);
        $this->assertEquals(2, $scheme->maximum);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2.000001);
    }

}
