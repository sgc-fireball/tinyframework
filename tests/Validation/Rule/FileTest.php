<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Http\UploadedFile;
use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\FileRule;
use TinyFramework\Validation\ValidationException;

class FileTest extends ValidationTestCase
{

    public function fileProvider(): array
    {
        return [
            [new UploadedFile([
                'name' => 'image.png',
                'type' => 'image/png',
                'size' => 100,
                'tmp_name' => dechex(time()),
                'error' => 0
            ]), true],
            [new UploadedFile([
                'name' => 'image.png',
                'type' => 'image/png',
                'size' => 0,
                'tmp_name' => dechex(time()),
                'error' => 10
            ]), false],
            [null, false],
            ['asd', false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider fileProvider
     */
    public function testFile(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new FileRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'file']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }

}
