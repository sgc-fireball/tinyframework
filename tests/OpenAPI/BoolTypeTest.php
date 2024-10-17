<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Types\BoolType;

class BoolTypeTest extends TestCase
{

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
        $scheme->validate(true);
        $scheme->validate(false);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(null);
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
        $scheme->validate(true);
        $scheme->validate(false);
        $scheme->validate(null);
        $this->expectException(OpenAPIException::class);
        $scheme->validate('abc');
    }

}
