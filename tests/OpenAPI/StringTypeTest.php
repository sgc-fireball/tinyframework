<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyFramework\Helpers\Uuid;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\OpenAPIValidator;
use TinyFramework\OpenAPI\Types\StringType;
use TinyFramework\WebToken\JWT;

class StringTypeTest extends TestCase
{

    private OpenAPIValidator $openAPIValidator;
    private ReflectionMethod $reflectionMethod;

    public function setUp(): void
    {
        $this->openAPIValidator = new OpenAPIValidator(new OpenAPI());
        $reflectionClass = new ReflectionClass($this->openAPIValidator);
        $this->reflectionMethod = $reflectionClass->getMethod('validateStringType');
        $this->reflectionMethod->setAccessible(true);
    }

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
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'a');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, str_repeat('a', 1024));
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
    }

    public function testNullable(): void
    {
        $scheme = StringType::parse(['nullable' => true]);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals(true, $scheme->nullable);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'a');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, str_repeat('a', 1024));
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, null);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, false);
    }

    public function testEnum(): void
    {
        $scheme = StringType::parse(['enum' => ['a', 'c']]);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals(['a', 'c'], $scheme->enum);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'a');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'c');
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'b');
    }

    public function testMinLength(): void
    {
        $scheme = StringType::parse(['minLength' => 5]);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals(5, $scheme->minLength);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, str_repeat('a', mt_rand(5, 1024)));
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, str_repeat('a', mt_rand(1, 4)));
    }

    public function testMaxLength(): void
    {
        $scheme = StringType::parse(['maxLength' => 5]);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals(5, $scheme->maxLength);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, str_repeat('a', mt_rand(1, 5)));
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, str_repeat('a', mt_rand(6, 1024)));
    }

    public function testPattern(): void
    {
        $scheme = StringType::parse(['pattern' => '/^\d+$/']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('/^\d+$/', $scheme->pattern);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '5');
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'a');
    }

    public function testFormatEmail(): void
    {
        $scheme = StringType::parse(['format' => 'email']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('email', $scheme->format);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'user@example.de');
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'user');
    }

    public function testHex(): void
    {
        $scheme = StringType::parse(['format' => 'hex']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('hex', $scheme->format);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '0');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '05');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '5f');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '5F');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'aA');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'ff');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'FF');
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '0g');
    }

    public function testJwt(): void
    {
        $scheme = StringType::parse(['format' => 'jwt']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('jwt', $scheme->format);
        $jwt = new JWT(JWT::ALG_HS256, random_bytes(32));
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, $jwt->encode());
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '123123.132123.123123');
    }

    public function testUuid(): void
    {
        $scheme = StringType::parse(['format' => 'uuid']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('uuid', $scheme->format);
        //$this->reflectionMethod->invoke($this->openAPIValidator, $scheme, Uuid::v1()); // @TODO error in v1
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, Uuid::v2());
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, Uuid::v3(Uuid::NAMESPACE_URL, 'https://www.google.de'));
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, Uuid::v4());
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, Uuid::v5(Uuid::NAMESPACE_URL, 'https://www.google.de'));
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, Uuid::v6());
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, Uuid::v7());
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, Uuid::v8());
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, 'gggggggg-gggg-gggg-gggg-gggggggggggggggg');
    }

    public function testDateTime(): void
    {
        $scheme = StringType::parse(['format' => 'date-time']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('date-time', $scheme->format);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2000-01-01 01:01:01');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2000-01-01T01:01:01');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2000-01-01 01:01:01.000');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2000-01-01T01:01:01.000');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2000-01-01 01:01:01.000000');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2000-01-01T01:01:01.000000');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2000-01-01 01:01:01.000000+00:00');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2000-01-01T01:01:01.000000-0200');
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '01.01.2000 01:01:01 UTC');
    }

    public function testDate(): void
    {
        $scheme = StringType::parse(['format' => 'date']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('date', $scheme->format);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2000-01-01');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '2000-12-31');
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '01.01.2000');
    }

    public function testTime(): void
    {
        $scheme = StringType::parse(['format' => 'time']);
        $this->assertInstanceOf(StringType::class, $scheme);
        $this->assertEquals('time', $scheme->format);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '00:00');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '00:00:00');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '23:59');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '23:59:59');
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '00');
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, '23');
    }

}
