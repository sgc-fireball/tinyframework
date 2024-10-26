<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\OpenAPIValidator;
use TinyFramework\OpenAPI\Types\IntegerType;

class IntegerTypeTest extends TestCase
{

    private OpenAPIValidator $openAPIValidator;
    private ReflectionMethod $reflectionMethod;

    public function setUp(): void
    {
        $this->openAPIValidator = new OpenAPIValidator(new OpenAPI());
        $reflectionClass = new ReflectionClass($this->openAPIValidator);
        $this->reflectionMethod = $reflectionClass->getMethod('validateIntegerType');
        $this->reflectionMethod->setAccessible(true);
    }

    public function testNormal(): void
    {
        $scheme = IntegerType::parse([]);
        $this->assertInstanceOf(IntegerType::class, $scheme);
        $this->assertEquals('integer', $scheme->type);
        $this->assertEquals(false, $scheme->nullable);
        $this->assertEquals(null, $scheme->format);
        $this->assertEquals(null, $scheme->default);
        $this->assertEquals(null, $scheme->example);
        $this->assertEquals(null, $scheme->minimum);
        $this->assertEquals(null, $scheme->exclusiveMinimum);
        $this->assertEquals(null, $scheme->exclusiveMaximum);
        $this->assertEquals(null, $scheme->maximum);
        $this->assertEquals(null, $scheme->xml);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '-2');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '-1');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '0');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '1');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, -2);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, -1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
    }

    public function testNullable(): void
    {
        $scheme = IntegerType::parse(['nullable' => true]);
        $this->assertInstanceOf(IntegerType::class, $scheme);
        $this->assertEquals(true, $scheme->nullable);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '-2');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '-1');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '0');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '1');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, -2);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, -1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'a');
    }

    public function testMinimum(): void
    {
        $scheme = IntegerType::parse(['minimum' => 0]);
        $this->assertInstanceOf(IntegerType::class, $scheme);
        $this->assertEquals(0, $scheme->minimum);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '1');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '0');
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, -1);
    }

    public function testExclusiveMinimum(): void
    {
        $scheme = IntegerType::parse(['exclusiveMinimum' => 0]);
        $this->assertInstanceOf(IntegerType::class, $scheme);
        $this->assertEquals(0, $scheme->exclusiveMinimum);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '1');
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0);
    }

    public function testExclusiveMaximum(): void
    {
        $scheme = IntegerType::parse(['exclusiveMaximum' => 2]);
        $this->assertInstanceOf(IntegerType::class, $scheme);
        $this->assertEquals(2, $scheme->exclusiveMaximum);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '0');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '1');
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2);
    }

    public function testMaximum(): void
    {
        $scheme = IntegerType::parse(['maximum' => 2]);
        $this->assertInstanceOf(IntegerType::class, $scheme);
        $this->assertEquals(2, $scheme->maximum);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 0);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '0');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 1);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '1');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 2);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2');
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 3);
    }

}
