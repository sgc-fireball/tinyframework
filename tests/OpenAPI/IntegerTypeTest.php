<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Types\IntegerType;

class IntegerTypeTest extends TestCase
{

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
        $scheme->validate(-2);
        $scheme->validate(-1);
        $scheme->validate(0);
        $scheme->validate(1);
        $scheme->validate(2);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(null);
    }

    public function testNullable(): void
    {
        $scheme = IntegerType::parse(['nullable' => true]);
        $this->assertInstanceOf(IntegerType::class, $scheme);
        $this->assertEquals(true, $scheme->nullable);
        $scheme->validate(-2);
        $scheme->validate(-1);
        $scheme->validate(0);
        $scheme->validate(1);
        $scheme->validate(2);
        $scheme->validate(null);
        $this->expectException(OpenAPIException::class);
        $scheme->validate('a');
    }

    public function testMinimum(): void
    {
        $scheme = IntegerType::parse(['minimum' => 0]);
        $this->assertInstanceOf(IntegerType::class, $scheme);
        $this->assertEquals(0, $scheme->minimum);
        $scheme->validate(2);
        $scheme->validate(1);
        $scheme->validate(0);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(-1);
    }

    public function testExclusiveMinimum(): void
    {
        $scheme = IntegerType::parse(['exclusiveMinimum' => 0]);
        $this->assertInstanceOf(IntegerType::class, $scheme);
        $this->assertEquals(0, $scheme->exclusiveMinimum);
        $scheme->validate(2);
        $scheme->validate(1);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(0);
    }

    public function testExclusiveMaximum(): void
    {
        $scheme = IntegerType::parse(['exclusiveMaximum' => 2]);
        $this->assertInstanceOf(IntegerType::class, $scheme);
        $this->assertEquals(2, $scheme->exclusiveMaximum);
        $scheme->validate(0);
        $scheme->validate(1);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(2);
    }

    public function testMaximum(): void
    {
        $scheme = IntegerType::parse(['maximum' => 2]);
        $this->assertInstanceOf(IntegerType::class, $scheme);
        $this->assertEquals(2, $scheme->maximum);
        $scheme->validate(0);
        $scheme->validate(1);
        $scheme->validate(2);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(3);
    }

}
