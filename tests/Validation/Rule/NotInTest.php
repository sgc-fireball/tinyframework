<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\InRule;
use TinyFramework\Validation\Rule\NotInRule;
use TinyFramework\Validation\ValidationException;

class NotInTest extends ValidationTestCase
{
    public function inProvider(): array
    {
        return [
            [2, 'not_in:1,2,3', false],
            ['a', 'not_in:a,b,c', false],
            [4, 'not_in:1,2,3', true],
            ['d', 'not_in:a,b,c', true],
        ];
    }

    /**
     * @param mixed $value
     * @param string $rule
     * @param bool $valid
     * @return void
     * @dataProvider inProvider
     */
    public function testNotIn(mixed $value, string $rule, bool $valid): void
    {
        try {
            $this->validator->addRule(new NotInRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => $rule]
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }
}
