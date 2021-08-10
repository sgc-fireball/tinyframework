<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\InRule;
use TinyFramework\Validation\ValidationException;

class InTest extends ValidationTestCase
{

    public function inProvider(): array
    {
        return [
            [2, 'in:1,2,3', true],
            ['a', 'in:a,b,c', true],
            [4, 'in:1,2,3', false],
            ['d', 'in:a,b,c', false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider inProvider
     */
    public function testInLineFile(mixed $value, string $rule, bool $valid): void
    {
        try {
            $this->validator->addRule(new InRule($this->translator));
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
