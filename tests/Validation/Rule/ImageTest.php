<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Http\UploadedFile;
use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\ImageRule;
use TinyFramework\Validation\ValidationException;

class ImageTest extends ValidationTestCase
{

    public function imageProvider(): array
    {
        return [
            [new UploadedFile([
                'name' => 'image.webp',
                'type' => 'image/webp',
                'size' => 100,
                'tmp_name' => 'data:image/webp;base64,UklGRh4AAABXRUJQVlA4TBEAAAAvBAABAAdQjraXpP+BiOh/AAA=',
                'error' => 0
            ]), true],
            [new UploadedFile([
                'name' => 'image.png',
                'type' => 'image/webp',
                'size' => 0,
                'tmp_name' => dechex(time()),
                'error' => 10
            ]), false],
            [new UploadedFile([
                'name' => 'image.pdf',
                'type' => 'application/pdf',
                'size' => 1,
                'tmp_name' => 'data:application/pdf;base64,',
                'error' => 0
            ]), false],
            [null, false],
            ['asd', false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider imageProvider
     */
    public function testImage(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new ImageRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'image']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }

}
