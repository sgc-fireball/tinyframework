<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Feature\Http;

use TinyFramework\Http\RequestValidator;
use TinyFramework\Tests\Feature\FeatureTestCase;

class RequestValidatorTest extends FeatureTestCase
{
    public function testEmpty()
    {
        $request = new class extends RequestValidator {
            public function rules(): array
            {
                return [];
            }
        };
        $this->assertTrue($request->validate());
        $this->assertEmpty($request->getErrorBag());
        $this->assertEmpty($request->safe());
    }

    public function testInvalid()
    {
        $request = new class extends RequestValidator {
            public function rules(): array
            {
                return ['id' => 'required'];
            }
        };
        $this->assertFalse($request->validate());
        $this->assertCount(1, $request->getErrorBag());
        $this->assertArrayHasKey('id', $request->getErrorBag());
        $this->assertIsArray($request->getErrorBag()['id']);
        $this->assertCount(1, $request->getErrorBag()['id']);
        $this->assertArrayHasKey(0, $request->getErrorBag()['id']);
        $this->assertIsString($request->getErrorBag()['id'][0]);
        $this->assertEmpty($request->safe());
    }

    public function testGet()
    {
        $request = new class extends RequestValidator {
            public function rules(): array
            {
                return [
                    'id' => 'required|numeric|min:5|max:5',
                ];
            }
        };
        $request->get(['id' => 5]);
        $request->validate();
        $this->assertEmpty($request->getErrorBag());
        $this->assertCount(1, $request->safe());
        $this->assertArrayHasKey('id', $request->safe());
        $this->assertIsNumeric($request->safe()['id']);
        $this->assertEquals(5, $request->safe()['id']);
    }

    public function testPost()
    {
        $request = new class extends RequestValidator {
            public function rules(): array
            {
                return [
                    'id' => 'required|numeric|min:5|max:5',
                ];
            }
        };
        $request->post(['id' => 5]);
        $this->assertTrue($request->validate());
        $this->assertEmpty($request->getErrorBag());
        $this->assertCount(1, $request->safe());
        $this->assertArrayHasKey('id', $request->safe());
        $this->assertIsNumeric($request->safe()['id']);
        $this->assertEquals(5, $request->safe()['id']);
    }
}
