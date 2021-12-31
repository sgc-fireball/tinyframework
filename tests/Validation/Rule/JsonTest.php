<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\JsonRule;
use TinyFramework\Validation\ValidationException;

class JsonTest extends ValidationTestCase
{
    public function jsonProvider(): array
    {
        return [
            [json_encode(null), true],
            [json_encode(true), true],
            [json_encode(false), true],
            [json_encode(-1), true],
            [json_encode(0), true],
            [json_encode(1), true],
            [json_encode('a'), true],
            [json_encode([1, 2, 3]), true],
            [json_encode([]), true],
            [json_encode((object)[]), true],
            [json_encode(['a' => 1]), true],
            ['a', false],
            [null, false],
            [false, false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider jsonProvider
     */
    public function testJson(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new JsonRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'json']
            );
            $this->assertTrue($valid, var_export($value, true));
        } catch (ValidationException $e) {
            $this->assertFalse($valid, var_export($value, true));
        }
    }
}
