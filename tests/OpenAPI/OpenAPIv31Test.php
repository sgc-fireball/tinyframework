<?php

declare(strict_types=1);

namespace TinyFramework\Tests\OpenAPI;

use PHPUnit\Framework\TestCase;
use TinyFramework\OpenAPI\Objects\Contact;
use TinyFramework\OpenAPI\Objects\Info;
use TinyFramework\OpenAPI\Objects\License;
use TinyFramework\OpenAPI\Objects\OpenAPI;

class OpenAPIv31Test extends TestCase
{

    public function testOpenAPIInfo(): void
    {
        $openAPI = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Title"
    version: 1.0.0
    summary: "API Summary"
    description: "API Description"
    termsOfService: "http://www.example.de"
    contact:
        name: "Contact Name"
        url: "http://www.example.de"
        email: "email@exmaple.de"
    license:
        name: "Apache 2.0"
        identifier: "Apache-2.0"
        url: "https://www.apache.org/licenses/LICENSE-2.0.txt"
EOF
        );
        $this->assertInstanceOf(OpenAPI::class, $openAPI);
        $this->assertEquals('3.1.0', $openAPI->openapi);

        $this->assertInstanceOf(Info::class, $openAPI->info);
        $this->assertEquals('1.0.0', $openAPI->info->version);
        $this->assertEquals('API Title', $openAPI->info->title);
        $this->assertEquals('API Summary', $openAPI->info->summary);
        $this->assertEquals('API Description', $openAPI->info->description);
        $this->assertEquals('http://www.example.de', $openAPI->info->termsOfService);

        $this->assertInstanceOf(Contact::class, $openAPI->info->contact);
        $this->assertEquals('Contact Name', $openAPI->info->contact->name);
        $this->assertEquals('http://www.example.de', $openAPI->info->contact->url);
        $this->assertEquals('email@exmaple.de', $openAPI->info->contact->email);

        $this->assertInstanceOf(License::class, $openAPI->info->license);
        $this->assertEquals('Apache 2.0', $openAPI->info->license->name);
        $this->assertEquals('Apache-2.0', $openAPI->info->license->identifier);
        $this->assertEquals('https://www.apache.org/licenses/LICENSE-2.0.txt', $openAPI->info->license->url);
    }

    public function testCacheable(): void
    {
        $openAPI1 = OpenAPI::parseYaml(
            <<<EOF
---
openapi: 3.1.0

info:
    title: "API Docu"
    version: 1.0.0
EOF
        );
        $openAPI2 = unserialize(serialize($openAPI1));
        $this->assertInstanceOf(OpenAPI::class, $openAPI2);
        $this->assertEquals($openAPI1->openapi, $openAPI2->openapi);
        $this->assertEquals($openAPI1->info->title, $openAPI2->info->title);
        $this->assertEquals($openAPI1->info->version, $openAPI2->info->version);
    }

}
