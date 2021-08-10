<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Http\UploadedFile;
use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\BetweenRule;
use TinyFramework\Validation\ValidationException;

class BetweenTest extends ValidationTestCase
{

    public function betweenProvider(): array
    {
        $uploaded = new UploadedFile([
            'name' => 'image.png',
            'type' => 'image/png',
            'size' => 100,
            'tmp_name' => dechex(time()),
            'error' => 0
        ]);

        return [
            [$uploaded, 0, 500, true],
            [$uploaded, 500, 1000, false],
            [$uploaded, 0, 50, false],

            ['test', 3, 5, true],
            ['a', 3, 5, false],
            ['testtest', 3, 5, false],

            [[1, 2, 3], 2, 4, true],
            [[1], 2, 4, false],
            [[1, 2, 3, 4, 5], 2, 4, false],

            [0, 0, 10, true],
            [5, 0, 10, true],
            [10, 0, 10, true],
            [11, 0, 10, false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider betweenProvider
     */
    public function testBetween(mixed $value, int $min, int $max, bool $valid): void
    {
        try {
            $this->validator->addRule(new BetweenRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'between:' . $min . ',' . $max]
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }

}
