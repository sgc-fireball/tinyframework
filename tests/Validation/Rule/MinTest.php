<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Http\UploadedFile;
use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\MinRule;
use TinyFramework\Validation\ValidationException;

class MinTest extends ValidationTestCase
{
    public function minProvider(): array
    {
        return [
            [6, 5, true],
            [6, 6, true],
            [5, 6, false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider minProvider
     */
    public function testMin(mixed $value, int $limit, bool $valid): void
    {
        try {
            $this->validator->addRule(new MinRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'min:' . $limit]
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }
}
