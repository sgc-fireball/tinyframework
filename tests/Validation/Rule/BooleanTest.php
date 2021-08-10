<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\BooleanRule;
use TinyFramework\Validation\ValidationException;

class BooleanTest extends ValidationTestCase
{

    public function booleanProvider(): array
    {
        return [
            ['ON', true],
            ['On', true],
            ['on', true],
            ['yes', true],
            ['y', true],
            ['1', true],
            [1, true],
            ['true', true],
            [true, true],
            ['OFF', true],
            ['Off', true],
            ['off', true],
            ['no', true],
            ['n', true],
            ['0', true],
            [0, true],
            ['false', true],
            [false, true],

            [null, false],
            ['null', false],
            [5, false],
            [-5, false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider booleanProvider
     */
    public function testBoolean(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new BooleanRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'boolean']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }

}
