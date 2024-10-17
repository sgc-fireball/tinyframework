<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\OpenAPI\OpenAPIException;
use TinyFramework\OpenAPI\Types\OneOfType;

class OneOfTypeTest extends TestCase
{

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
        $scheme->validate(['id' => 1]);
        $scheme->validate(['name' => 'a']);
        $this->expectException(OpenAPIException::class);
        $scheme->validate(['id' => 1, 'name' => 'a']);
    }

}
