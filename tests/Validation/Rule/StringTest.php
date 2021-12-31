<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\StringRule;
use TinyFramework\Validation\ValidationException;

class StringTest extends ValidationTestCase
{
    public function stringProvider(): array
    {
        return [
            [true, false],
            [false, false],
            [null, false],
            [[], false],
            [(object)[], false],
            [-1.5, false],
            [-1, false],
            [0, false],
            [0.0, false],
            [1, false],
            [1.5, false],
            ['', true],
            ['abc', true],
            ['1', true],
            ['1.5', true],
            ['1,5', true],
            [new class() implements \Stringable {
                public function __toString()
                {
                    return 'ok';
                }
            }, true],
            [new class() {
                public function __toString()
                {
                    return 'ok';
                }
            }, true],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider stringProvider
     */
    public function testString(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new StringRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'string']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }
}
