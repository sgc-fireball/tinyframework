<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Types\ArrayType;
use TinyFramework\OpenAPI\Types\IntegerType;

class ArrayTypeTest extends TestCase
{

    public function testNormal(): void
    {
        $scheme = ArrayType::parse([]);
        $this->assertInstanceOf(ArrayType::class, $scheme);
        $this->assertEquals('array', $scheme->type);
        $this->assertEquals(false, $scheme->nullable);
        $this->assertEquals(null, $scheme->minItems);
        $this->assertEquals(null, $scheme->maxItems);
        $this->assertEquals(false, $scheme->uniqueItems);
        $scheme->validate([]);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(null);
    }

    public function testNullable(): void
    {
        $scheme = ArrayType::parse(['nullable' => true]);
        $this->assertInstanceOf(ArrayType::class, $scheme);
        $this->assertEquals(true, $scheme->nullable);
        $scheme->validate([]);
        $scheme->validate(null);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(false);
    }

    public function testMinItems(): void
    {
        $scheme = ArrayType::parse(['minItems' => 2]);
        $this->assertInstanceOf(ArrayType::class, $scheme);
        $this->assertEquals(2, $scheme->minItems);
        $scheme->validate([1, 2,]);
        $this->expectException(OpenAPIException::class);
        $scheme->validate([1]);
    }

    public function testMaxItems(): void
    {
        $scheme = ArrayType::parse(['maxItems' => 2]);
        $this->assertInstanceOf(ArrayType::class, $scheme);
        $this->assertEquals(2, $scheme->maxItems);
        $scheme->validate([1, 2]);
        $this->expectException(OpenAPIException::class);
        $scheme->validate([1, 2, 3]);
    }

    public function testUniqueItems(): void
    {
        $scheme = ArrayType::parse(['uniqueItems' => true]);
        $this->assertInstanceOf(ArrayType::class, $scheme);
        $this->assertEquals(true, $scheme->uniqueItems);
        $scheme->validate([1, 2, 3, 4]);
        $this->expectException(OpenAPIException::class);
        $scheme->validate([1, 2, 2, 3]);
    }

    public function testItems(): void
    {
        $scheme = ArrayType::parse(['items' => ['type' => 'integer']]);
        $this->assertInstanceOf(ArrayType::class, $scheme);
        $this->assertInstanceOf(IntegerType::class, $scheme->items);
        $scheme->validate([1, 2, 3, 4]);
        $this->expectException(OpenAPIException::class);
        $scheme->validate([1, 2, 'a', 3]);
    }

}
