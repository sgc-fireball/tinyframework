<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\OpenAPIValidator;
use TinyFramework\OpenAPI\Types\AnyOfType;

class AnyOfTypeTest extends TestCase
{

    private OpenAPIValidator $openAPIValidator;
    private ReflectionMethod $reflectionMethod;

    public function setUp(): void
    {
        $this->openAPIValidator = new OpenAPIValidator(new OpenAPI());
        $reflectionClass = new ReflectionClass($this->openAPIValidator);
        $this->reflectionMethod = $reflectionClass->getMethod('validateAnyOfType');
        $this->reflectionMethod->setAccessible(true);
    }

    public function testAnyOf(): void
    {
        $scheme = AnyOfType::parse([
            'anyOf' => [
                [
                    'type' => 'object',
                    'required' => ['id'],
                    'properties' => [
                        'id' => [
                            'type' => 'integer',
                        ],
                    ],
                ],
                [
                    'type' => 'object',
                    'required' => ['id'],
                    'properties' => [
                        'id' => [
                            'type' => 'boolean',
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertInstanceOf(AnyOfType::class, $scheme);
        $this->openAPIValidator = new OpenAPIValidator(new OpenAPI());
        $reflectionClass = new ReflectionClass($this->openAPIValidator);
        $this->reflectionMethod = $reflectionClass->getMethod('validateAnyOfType');
        $this->reflectionMethod->setAccessible(true);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => '-1']);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => -1]);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 0]);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => '0']);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 1]);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => '1']);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => false]);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => true]);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 1.5]);
    }

}
