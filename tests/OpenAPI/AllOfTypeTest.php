<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\OpenAPIValidator;
use TinyFramework\OpenAPI\Types\AllOfType;

class AllOfTypeTest extends TestCase
{

    private OpenAPIValidator $openAPIValidator;
    private ReflectionMethod $reflectionMethod;

    public function setUp(): void
    {
        $this->openAPIValidator = new OpenAPIValidator(new OpenAPI());
        $reflectionClass = new ReflectionClass($this->openAPIValidator);
        $this->reflectionMethod = $reflectionClass->getMethod('validateAllOfType');
        $this->reflectionMethod->setAccessible(true);
    }

    public function testAllOf(): void
    {
        $scheme = AllOfType::parse([
            'allOf' => [
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

        $this->assertInstanceOf(AllOfType::class, $scheme);
        $openAPIValidator = new OpenAPIValidator(new OpenAPI());
        $reflectionClass = new ReflectionClass($openAPIValidator);
        $reflectionMethod = $reflectionClass->getMethod('validateAllOfType');
        $reflectionMethod->setAccessible(true);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 1, 'name' => 'a']);
        $this->expectException(OpenAPIException::class);
        $this->reflectionMethod->invoke($this->openAPIValidator, $scheme, ['id' => 1]);
    }

}
