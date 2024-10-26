<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\OpenAPIValidator;
use TinyFramework\OpenAPI\Types\ArrayType;
use TinyFramework\OpenAPI\Types\IntegerType;

class ArrayTypeTest extends TestCase
{

    private OpenAPIValidator $openAPIValidator;
    private ReflectionMethod $reflectionMethod;

    public function setUp(): void
    {
        $this->openAPIValidator = new OpenAPIValidator(new OpenAPI());
        $reflectionClass = new ReflectionClass($this->openAPIValidator);
        $this->reflectionMethod = $reflectionClass->getMethod('validateSchema');
        $this->reflectionMethod->setAccessible(true);
    }

    public function testNormal(): void
    {
        $scheme = ArrayType::parse([]);
        $this->assertInstanceOf(ArrayType::class, $scheme);
        $this->assertEquals('array', $scheme->type);
        $this->assertEquals(false, $scheme->nullable);
        $this->assertEquals(null, $scheme->minItems);
        $this->assertEquals(null, $scheme->maxItems);
        $this->assertEquals(false, $scheme->uniqueItems);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, []);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
    }

    public function testNullable(): void
    {
        $scheme = ArrayType::parse(['nullable' => true]);
        $this->assertInstanceOf(ArrayType::class, $scheme);
        $this->assertEquals(true, $scheme->nullable);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, []);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, false);
    }

    public function testMinItems(): void
    {
        $scheme = ArrayType::parse(['minItems' => 2]);
        $this->assertInstanceOf(ArrayType::class, $scheme);
        $this->assertEquals(2, $scheme->minItems);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, [1, 2,]);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, [1]);
    }

    public function testMaxItems(): void
    {
        $scheme = ArrayType::parse(['maxItems' => 2]);
        $this->assertInstanceOf(ArrayType::class, $scheme);
        $this->assertEquals(2, $scheme->maxItems);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, [1, 2]);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, [1, 2, 3]);
    }

    public function testUniqueItems(): void
    {
        $scheme = ArrayType::parse(['uniqueItems' => true]);
        $this->assertInstanceOf(ArrayType::class, $scheme);
        $this->assertEquals(true, $scheme->uniqueItems);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, [1, 2, 3, 4]);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, [1, 2, 2, 3]);
    }

    public function testItems(): void
    {
        $scheme = ArrayType::parse(['items' => ['type' => 'integer']]);
        $this->assertInstanceOf(ArrayType::class, $scheme);
        $this->assertInstanceOf(IntegerType::class, $scheme->items);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, [1, 2, 3, 4]);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, [1, 2, 'a', 3]);
    }

}
