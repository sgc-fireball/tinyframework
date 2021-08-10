<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\ArrayRule;
use TinyFramework\Validation\ValidationException;

class ArrayTest extends ValidationTestCase
{

    public function arrayProvider(): array
    {
        return [
            [[1, 2, 3], true],
            ['null', false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider arrayProvider
     */
    public function testArray(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new ArrayRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'array']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }

}
