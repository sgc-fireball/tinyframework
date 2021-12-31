<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Http\UploadedFile;
use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\MaxRule;
use TinyFramework\Validation\ValidationException;

class MaxTest extends ValidationTestCase
{
    public function maxProvider(): array
    {
        return [
            [6, 5, false],
            [5, 5, true],
            [5, 6, true],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider maxProvider
     */
    public function testMax(mixed $value, int $limit, bool $valid): void
    {
        try {
            $this->validator->addRule(new MaxRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'max:' . $limit]
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }
}
