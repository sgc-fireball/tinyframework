<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\NumericRule;
use TinyFramework\Validation\ValidationException;

class NumericTest extends ValidationTestCase
{
    public function numericProvider(): array
    {
        return [
            [true, false],
            [null, false],
            ['', false],
            ['asd', false],
            ['123', false],
            ['123.12', false],
            [123, true],
            [123.12, true],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider numericProvider
     */
    public function testNumeric(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new NumericRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'numeric']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }
}
