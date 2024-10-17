<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Types\AllOfType;

class AllOfTypeTest extends TestCase
{

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
            ]
        ]);
        $this->assertInstanceOf(AllOfType::class, $scheme);
        $scheme->validate(['id' => 1, 'name' => 'a']);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(['id' => 1]);
    }

}
