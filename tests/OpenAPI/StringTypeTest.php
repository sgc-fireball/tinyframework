<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\Helpers\Uuid;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Types\StringType;
use TinyFramework\WebToken\JWT;

class StringTypeTest extends TestCase
{

    public function testNormal(): void
    {
        $scheme = StringType::parse([]);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('string', $scheme->type);
        $this->assertEquals(null, $scheme->format);
        $this->assertEquals(false, $scheme->nullable);
        $this->assertEquals(null, $scheme->description);
        $this->assertEquals(null, $scheme->default);
        $this->assertEquals(null, $scheme->example);
        $this->assertEquals(null, $scheme->enum);
        $this->assertEquals(null, $scheme->minLength);
        $this->assertEquals(null, $scheme->maxLength);
        $this->assertEquals(null, $scheme->pattern);
        $this->assertEquals(null, $scheme->xml);
        $scheme->validate('a');
        $scheme->validate(str_repeat('a', 1024));
        $this->expectException(OpenAPIException::class);
        $scheme->validate(null);
    }

    public function testNullable(): void
    {
        $scheme = StringType::parse(['nullable' => true]);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals(true, $scheme->nullable);
        $scheme->validate('a');
        $scheme->validate(str_repeat('a', 1024));
        $scheme->validate(null);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(false);
    }

    public function testEnum(): void
    {
        $scheme = StringType::parse(['enum' => ['a', 'c']]);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals(['a', 'c'], $scheme->enum);
        $scheme->validate('a');
        $scheme->validate('c');
        $this->expectException(OpenAPIException::class);
        $scheme->validate('b');
    }

    public function testMinLength(): void
    {
        $scheme = StringType::parse(['minLength' => 5]);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals(5, $scheme->minLength);
        $scheme->validate(str_repeat('a', mt_rand(5, 1024)));
        $this->expectException(OpenAPIException::class);
        $scheme->validate(str_repeat('a', mt_rand(1, 4)));
    }

    public function testMaxLength(): void
    {
        $scheme = StringType::parse(['maxLength' => 5]);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals(5, $scheme->maxLength);
        $scheme->validate(str_repeat('a', mt_rand(1, 5)));
        $this->expectException(OpenAPIException::class);
        $scheme->validate(str_repeat('a', mt_rand(6, 1024)));
    }

    public function testPattern(): void
    {
        $scheme = StringType::parse(['pattern' => '/^\d+$/']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('/^\d+$/', $scheme->pattern);
        $scheme->validate('5');
        $this->expectException(OpenAPIException::class);
        $scheme->validate('a');
    }

    public function testFormatEmail(): void
    {
        $scheme = StringType::parse(['format' => 'email']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('email', $scheme->format);
        $scheme->validate('user@example.de');
        $this->expectException(OpenAPIException::class);
        $scheme->validate('user');
    }

    public function testHex(): void
    {
        $scheme = StringType::parse(['format' => 'hex']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('hex', $scheme->format);
        $scheme->validate('0');
        $scheme->validate('05');
        $scheme->validate('5f');
        $scheme->validate('5F');
        $scheme->validate('aA');
        $scheme->validate('ff');
        $scheme->validate('FF');
        $this->expectException(OpenAPIException::class);
        $scheme->validate('0g');
    }

    public function testJwt(): void
    {
        $scheme = StringType::parse(['format' => 'jwt']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('jwt', $scheme->format);
        $jwt = new JWT(JWT::ALG_HS256, random_bytes(32));
        $scheme->validate($jwt->encode());
        $this->expectException(OpenAPIException::class);
        $scheme->validate('123123.132123.123123');
    }

    public function testUuid(): void
    {
        $scheme = StringType::parse(['format' => 'uuid']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('uuid', $scheme->format);
        //$scheme->validate(Uuid::v1()); // @TODO error in v1
        $scheme->validate(Uuid::v2());
        $scheme->validate(Uuid::v3(Uuid::NAMESPACE_URL, 'https://www.google.de'));
        $scheme->validate(Uuid::v4());
        $scheme->validate(Uuid::v5(Uuid::NAMESPACE_URL, 'https://www.google.de'));
        $scheme->validate(Uuid::v6());
        $scheme->validate(Uuid::v7());
        $scheme->validate(Uuid::v8());
        $this->expectException(OpenAPIException::class);
        $scheme->validate('gggggggg-gggg-gggg-gggg-gggggggggggggggg');
    }

    public function testDateTime(): void
    {
        $scheme = StringType::parse(['format' => 'date-time']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('date-time', $scheme->format);
        $scheme->validate('2000-01-01 01:01:01');
        $scheme->validate('2000-01-01T01:01:01');
        $scheme->validate('2000-01-01 01:01:01.000');
        $scheme->validate('2000-01-01T01:01:01.000');
        $scheme->validate('2000-01-01 01:01:01.000000');
        $scheme->validate('2000-01-01T01:01:01.000000');
        $scheme->validate('2000-01-01 01:01:01.000000+00:00');
        $scheme->validate('2000-01-01T01:01:01.000000-0200');
        $this->expectException(OpenAPIException::class);
        $scheme->validate('01.01.2000 01:01:01 UTC');
    }

    public function testDate(): void
    {
        $scheme = StringType::parse(['format' => 'date']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('date', $scheme->format);
        $scheme->validate('2000-01-01');
        $scheme->validate('2000-12-31');
        $this->expectException(OpenAPIException::class);
        $scheme->validate('01.01.2000');
    }

    public function testTime(): void
    {
        $scheme = StringType::parse(['format' => 'time']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('time', $scheme->format);
        $scheme->validate('00:00');
        $scheme->validate('00:00:00');
        $scheme->validate('23:59');
        $scheme->validate('23:59:59');
        $this->expectException(OpenAPIException::class);
        $scheme->validate('00');
        $scheme->validate('23');
    }

}
