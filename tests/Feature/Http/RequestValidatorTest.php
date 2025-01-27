<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Feature\Http;

use TinyFramework\Http\Request;
use TinyFramework\Http\RequestValidator;
use TinyFramework\Tests\Feature\FeatureTestCase;

class NoRulesValidator extends RequestValidator
{
    public function rules(): array
    {
        return [];
    }
}

class IDValidator extends RequestValidator
{
    public function rules(): array
    {
        return [
            'id' => 'required|numeric|min:5|max:5',
        ];
    }
}

class RequestValidatorTest extends FeatureTestCase
{
    public function testEmpty()
    {
        /** @var IDValidator|Request $request */
        container()->singleton('request', new Request());
        $request = container(NoRulesValidator::class);
        $this->assertTrue($request->validate());
        $this->assertEmpty($request->getErrorBag());
        $this->assertEmpty($request->safe());
    }

    public function testInvalid()
    {
        /** @var IDValidator|Request $request */
        container()->singleton('request', new Request());
        $request = container(IDValidator::class);
        $this->assertFalse($request->validate());
        $this->assertCount(2, $request->getErrorBag());
        $this->assertArrayHasKey('id', $request->getErrorBag());
        $this->assertIsArray($request->getErrorBag()['id']);
        $this->assertCount(3, $request->getErrorBag()['id']);
        $this->assertArrayHasKey(0, $request->getErrorBag()['id']);
        $this->assertIsString($request->getErrorBag()['id'][0]);
        $this->assertEmpty($request->safe());
    }

    public function testGet()
    {
        container()->singleton('request', new Request());
        /** @var IDValidator|Request $request */
        $request = container(IDValidator::class);
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
        container()->singleton('request', new Request());
        /** @var IDValidator|Request $request */
        $request = container(IDValidator::class);
        $request->post(['id' => 5]);
        $this->assertTrue($request->validate());
        $this->assertEmpty($request->getErrorBag());
        $this->assertCount(1, $request->safe());
        $this->assertArrayHasKey('id', $request->safe());
        $this->assertIsNumeric($request->safe()['id']);
        $this->assertEquals(5, $request->safe()['id']);
    }
}
