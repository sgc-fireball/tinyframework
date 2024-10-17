<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Types\AnyOfType;

class AnyOfTypeTest extends TestCase
{

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
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertInstanceOf(AnyOfType::class, $scheme);
        $scheme->validate(['id' => 1]);
        $scheme->validate(['id' => 'a']);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(['id' => 1.5]);
    }

}
