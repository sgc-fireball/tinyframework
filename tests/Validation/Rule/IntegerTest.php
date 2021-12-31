<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\IntegerRule;
use TinyFramework\Validation\ValidationException;

class IntegerTest extends ValidationTestCase
{
    public function integerProvider(): array
    {
        return [
            [true, false],
            [null, false],
            ['', false],
            ['asd', false],
            ['-123.12', false],
            ['-123', false],
            ['0', false],
            ['123', false],
            ['123.12', false],
            [-123, true],
            [0, true],
            [123, true],
            [123.12, false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider integerProvider
     */
    public function testInteger(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new IntegerRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'integer']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }
}
