<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\ConfirmedRule;
use TinyFramework\Validation\ValidationException;

class ConfirmedTest extends ValidationTestCase
{

    public function booleanProvider(): array
    {
        return [
            ['123', '123', true],
            ['123', '', false],
            ['123', null, false],
        ];
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @param bool $valid
     * @return void
     * @dataProvider booleanProvider
     */
    public function testConfirmed(mixed $value1, mixed $value2, bool $valid): void
    {
        try {
            $this->validator->addRule(new ConfirmedRule($this->translator));
            $this->validator->validate(
                ['field' => $value1, 'field_confirmed' => $value2],
                ['field' => 'confirmed']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }

}
