<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Http\UploadedFile;
use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\RequiredRule;
use TinyFramework\Validation\ValidationException;

class RequiredTest extends ValidationTestCase
{
    public function requiredProvider(): array
    {
        return [
            [-1, true],
            [0, true],
            [1, true],
            ['A', true],
            ['a', true],
            [['a'], true],
            [['a' => 1], true],
            [(object)[], true],
            [new UploadedFile([
                'name' => 'image.png',
                'type' => 'image/png',
                'size' => 100,
                'tmp_name' => dechex(time()),
                'error' => 0
            ]), true],

            [[], false],
            [null, false],
            ['', false],
            [new UploadedFile([
                'name' => 'image.png',
                'type' => 'image/png',
                'size' => 0,
                'tmp_name' => dechex(time()),
                'error' => 10
            ]), false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider requiredProvider
     */
    public function testRequired(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new RequiredRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'required']
            );
            $this->assertTrue($valid, var_export($value, true));
        } catch (ValidationException $e) {
            $this->assertFalse($valid, var_export($value, true));
        }
    }
}
