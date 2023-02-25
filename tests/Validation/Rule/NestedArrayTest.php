<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\ArrayRule;
use TinyFramework\Validation\Rule\BooleanRule;
use TinyFramework\Validation\Rule\MaxRule;
use TinyFramework\Validation\Rule\MinRule;
use TinyFramework\Validation\Rule\RequiredRule;
use TinyFramework\Validation\ValidationException;

class NestedArrayTest extends ValidationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->validator->addRule(new ArrayRule($this->translator));
        $this->validator->addRule(new RequiredRule($this->translator));
        $this->validator->addRule(new BooleanRule($this->translator));
        $this->validator->addRule(new MinRule($this->translator));
        $this->validator->addRule(new MaxRule($this->translator));
    }

    public function testNestedArray(): void
    {
        $result = $this->validator->validate(
            [
                'data' => [
                    'yes' => true,
                    'no' => false,
                    'maybe' => 'maybe',
                ],
            ],
            [
                'data' => 'required|array|min:2|max:3',
                'data.yes' => 'required|boolean',
                'data.no' => 'required|boolean',
            ]
        );
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(2, $result['data']);
        $this->assertArrayHasKey('yes', $result['data']);
        $this->assertTrue($result['data']['yes']);
        $this->assertArrayHasKey('no', $result['data']);
        $this->assertFalse($result['data']['no']);
    }

    public function testNestedWildcardArray(): void
    {
        $result = $this->validator->validate(
            [
                'data' => [
                    ['yes' => true, 'no' => false, 'maybe' => 'maybe'],
                    ['yes' => true, 'no' => false, 'maybe' => 'maybe'],
                ],
            ],
            [
                'data' => 'required|array|min:2|max:2',
                'data.*' => 'required|array|min:3|max:3',
                'data.*.yes' => 'required|boolean',
                'data.*.no' => 'required|boolean',
            ]
        );
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(2, $result['data']);
        for ($i = 0; $i < 2; $i++) {
            $this->assertIsArray($result['data'][$i]);
            $this->assertCount(2, $result['data'][$i]);
            $this->assertArrayHasKey('yes', $result['data'][$i]);
            $this->assertTrue($result['data'][$i]['yes']);
            $this->assertArrayHasKey('no', $result['data'][$i]);
            $this->assertFalse($result['data'][$i]['no']);
        }
    }

    public function testDeepNestedWildcardArray(): void
    {
        $result = $this->validator->validate(
            [
                'data' => [
                    [
                        'data' => [
                            'yes' => true,
                            'no' => false,
                            'maybe' => 'maybe',
                        ],
                    ],
                    [
                        'data' => [
                            'yes' => true,
                            'no' => false,
                            'maybe' => 'maybe',
                        ],
                    ],
                ],
            ],
            [
                'data' => 'required|array|min:2|max:2',
                'data.*' => 'required|array|min:1|max:1',
                'data.*.data' => 'required|array|min:3|max:3',
                'data.*.data.yes' => 'required|boolean',
                'data.*.data.no' => 'required|boolean',
            ]
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(2, $result['data']);
        for ($i = 0; $i < 2; $i++) {
            $this->assertArrayHasKey($i, $result['data']);
            $this->assertIsArray($result['data'][$i]);
            $this->assertArrayHasKey('data', $result['data'][$i]);
            $this->assertIsArray($result['data'][$i]['data']);
            $this->assertCount(2, $result['data'][$i]['data']);
            $this->assertArrayHasKey('yes', $result['data'][$i]['data']);
            $this->assertTrue($result['data'][$i]['data']['yes']);
            $this->assertArrayHasKey('no', $result['data'][$i]['data']);
            $this->assertFalse($result['data'][$i]['data']['no']);
        }
    }
}
