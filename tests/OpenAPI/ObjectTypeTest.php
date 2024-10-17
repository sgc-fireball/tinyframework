<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Types\ObjectType;
use TinyFramework\OpenAPI\Types\IntegerType;

class ObjectTypeTest extends TestCase
{

    public function testNormal(): void
    {
        $scheme = ObjectType::parse([]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->assertEquals('object', $scheme->type);
        $this->assertEquals(false, $scheme->nullable);
        $this->assertEquals([], $scheme->required);
        $this->assertEquals(true, $scheme->additionalProperties);
        $scheme->validate([]);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(null);
    }

    public function testNullable(): void
    {
        $scheme = ObjectType::parse(['nullable' => true]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->assertEquals(true, $scheme->nullable);
        $scheme->validate(null);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(false);
    }

    public function testMinProperties(): void
    {
        $scheme = ObjectType::parse(['minProperties' => 5]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->assertEquals(5, $scheme->minProperties);
        $scheme->validate(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);
        $scheme->validate(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6]);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
    }

    public function testMaxProperties(): void
    {
        $scheme = ObjectType::parse(['maxProperties' => 5]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->assertEquals(5, $scheme->maxProperties);
        $scheme->validate(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
        $scheme->validate(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6]);
    }

    public function testRequired(): void
    {
        $scheme = ObjectType::parse(['required' => ['name']]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $this->assertEquals(['name'], $scheme->required);
        $scheme->validate(['name' => 'NameA']);
        $scheme->validate(['id' => 1, 'name' => 'NameA']);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(['id' => 2]);
    }

    public function testProperties(): void
    {
        $scheme = ObjectType::parse(['properties' => ['id' => ['type' => 'integer']]]);
        $this->assertInstanceOf(ObjectType::class, $scheme);
        $scheme->validate(['id' => 1]);
        $scheme->validate(['id' => 2, 'name' => 'NameA']);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(['id' => 'a']);
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
        $scheme->validate(['id' => 1]);
        $scheme->validate(['id' => 2]);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(['id' => 3, 'name' => 'NameA']);
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
        $scheme->validate(['id' => 1]);
        $scheme->validate(['id' => 2, 'additional' => -1 * PHP_INT_MAX]);
        $scheme->validate(['id' => 2, 'name' => 0]);
        $scheme->validate(['id' => 2, 'additional' => PHP_INT_MAX]);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(['id' => 3, 'name' => 'NameA']);
    }
}
