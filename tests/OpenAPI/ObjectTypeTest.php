<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\OpenAPIValidator;
use TinyFramework\OpenAPI\Types\ObjectType;
use TinyFramework\OpenAPI\Types\IntegerType;

class ObjectTypeTest extends TestCase
{

    private OpenAPIValidator $openAPIValidator;
    private ReflectionMethod $reflectionMethod;

    public function setUp(): void
    {
        $this->openAPIValidator = new OpenAPIValidator(new OpenAPI());
        $reflectionClass = new ReflectionClass($this->openAPIValidator);
        $this->reflectionMethod = $reflectionClass->getMethod('validateObjectType');
        $this->reflectionMethod->setAccessible(true);
    }

    public function testNormal(): void
    {
        $scheme = ObjectType::parse([]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->assertEquals('object', $scheme->type);
        $this->assertEquals(false, $scheme->nullable);
        $this->assertEquals([], $scheme->required);
        $this->assertEquals(true, $scheme->additionalProperties);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, []);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
    }

    public function testNullable(): void
    {
        $scheme = ObjectType::parse(['nullable' => true]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->assertEquals(true, $scheme->nullable);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, false);
    }

    public function testMinProperties(): void
    {
        $scheme = ObjectType::parse(['minProperties' => 5]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->assertEquals(5, $scheme->minProperties);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6]);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
    }

    public function testMaxProperties(): void
    {
        $scheme = ObjectType::parse(['maxProperties' => 5]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->assertEquals(5, $scheme->maxProperties);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6]);
    }

    public function testRequired(): void
    {
        $scheme = ObjectType::parse(['required' => ['name']]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->assertEquals(['name'], $scheme->required);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['name' => 'NameA']);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 1, 'name' => 'NameA']);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 2]);
    }

    public function testProperties(): void
    {
        $scheme = ObjectType::parse(['properties' => ['id' => ['type' => 'integer']]]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 1]);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 2, 'name' => 'NameA']);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 'a']);
    }

    public function testAdditionalPropertiesForbidden(): void
    {
        $scheme = ObjectType::parse([
            'required' => [
                'id',
            ],
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
            ],
            'additionalProperties' => false,
        ]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->assertEquals(false, $scheme->additionalProperties);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 1]);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 2]);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 3, 'name' => 'NameA']);
    }

    public function testAdditionalPropertiesDefined(): void
    {
        $scheme = ObjectType::parse([
            'required' => [
                'id',
            ],
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
            ],
            'additionalProperties' => [
                'type' => 'integer',
            ],
        ]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 1]);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 2, 'additional' => -1 * PHP_INT_MAX]);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 2, 'name' => 0]);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 2, 'additional' => PHP_INT_MAX]);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 3, 'name' => 'NameA']);
    }
}
