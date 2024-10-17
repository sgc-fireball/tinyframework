<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Types\NumberType;

class NumberTypeTest extends TestCase
{

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
        $scheme->validate(-2.3);
        $scheme->validate(-1);
        $scheme->validate(0);
        $scheme->validate(1);
        $scheme->validate(2.3);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(null);
    }

    public function testNullable(): void
    {
        $scheme = NumberType::parse(['nullable' => true]);
        $this->assertInstanceOf(NumberType::class, $scheme);
        $this->assertEquals(true, $scheme->nullable);
        $scheme->validate(-2.3);
        $scheme->validate(-1);
        $scheme->validate(0);
        $scheme->validate(1);
        $scheme->validate(2.3);
        $scheme->validate(null);
        $this->expectException(OpenAPIException::class);
        $scheme->validate('a');
    }

    public function testMinimum(): void
    {
        $scheme = NumberType::parse(['minimum' => 0]);
        $this->assertInstanceOf(NumberType::class, $scheme);
        $this->assertEquals(0, $scheme->minimum);
        $scheme->validate(2.3);
        $scheme->validate(1);
        $scheme->validate(0);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(-0.1);
    }

    public function testExclusiveMinimum(): void
    {
        $scheme = NumberType::parse(['exclusiveMinimum' => 0]);
        $this->assertInstanceOf(NumberType::class, $scheme);
        $this->assertEquals(0, $scheme->exclusiveMinimum);
        $scheme->validate(2.3);
        $scheme->validate(1);
        $scheme->validate(0.000001);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(0);
    }

    public function testExclusiveMaximum(): void
    {
        $scheme = NumberType::parse(['exclusiveMaximum' => 2]);
        $this->assertInstanceOf(NumberType::class, $scheme);
        $this->assertEquals(2, $scheme->exclusiveMaximum);
        $scheme->validate(0);
        $scheme->validate(1);
        $scheme->validate(1.999999);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(2);
    }

    public function testMaximum(): void
    {
        $scheme = NumberType::parse(['maximum' => 2]);
        $this->assertInstanceOf(NumberType::class, $scheme);
        $this->assertEquals(2, $scheme->maximum);
        $scheme->validate(0);
        $scheme->validate(1);
        $scheme->validate(2);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(2.000001);
    }

}
