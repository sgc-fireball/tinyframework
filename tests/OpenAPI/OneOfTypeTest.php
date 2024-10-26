<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\OpenAPIValidator;
use TinyFramework\OpenAPI\Types\OneOfType;

class OneOfTypeTest extends TestCase
{

    private OpenAPIValidator $openAPIValidator;
    private ReflectionMethod $reflectionMethod;

    public function setUp(): void
    {
        $this->openAPIValidator = new OpenAPIValidator(new OpenAPI());
        $reflectionClass = new ReflectionClass($this->openAPIValidator);
        $this->reflectionMethod = $reflectionClass->getMethod('validateOneOfType');
        $this->reflectionMethod->setAccessible(true);
    }

    public function testAnyOf(): void
    {
        $scheme = OneOfType::parse([
            'oneOf' => [
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
                    'required' => ['name'],
                    'properties' => [
                        'name' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertInstanceOf(OneOfType::class, $scheme);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 1]);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['name' => 'a']);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 1, 'name' => 'a']);
    }

}
