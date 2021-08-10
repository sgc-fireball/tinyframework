<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\Ipv4Rule;
use TinyFramework\Validation\Rule\NullableRule;
use TinyFramework\Validation\ValidationException;

class NullableTest extends ValidationTestCase
{

    public function nullableProvider(): array
    {
        return [
            [null, 'nullable|string', true],
            ['a', 'nullable|ipv4', false],
        ];
    }

    /**
     * @param mixed $value
     * @param string $rule
     * @param bool $valid
     * @return void
     * @dataProvider nullableProvider
     */
    public function testNullable(mixed $value, string $rule, bool $valid): void
    {
        try {
            $this->validator->addRule(new NullableRule($this->translator));
            $this->validator->addRule(new Ipv4Rule($this->translator));
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
