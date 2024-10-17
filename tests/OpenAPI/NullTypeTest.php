<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Types\NullType;

class NullTypeTest extends TestCase
{

    public function testNull(): void
    {
        $scheme = NullType::parse([
            'description' => 'description',
        ]);
        $this->assertInstanceOf(NullType::class, $scheme);
        $this->assertTrue($scheme->nullable);
        $this->assertEquals('description', $scheme->description);
        $this->assertNull($scheme->default);
        $this->assertNull($scheme->example);
        $scheme->validate(null);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(false);
    }

}
