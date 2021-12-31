<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\FloatRule;
use TinyFramework\Validation\ValidationException;

class FloatTest extends ValidationTestCase
{
    public function floatProvider(): array
    {
        return [
            [true, false],
            [null, false],
            ['', false],
            ['asd', false],
            ['123', false],
            ['123.12', false],
            [123, false],
            [123.12, true],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider floatProvider
     */
    public function testFloat(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new FloatRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'float']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }
}
