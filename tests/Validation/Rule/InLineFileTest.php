<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Http\UploadedFile;
use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\InLineFileRule;
use TinyFramework\Validation\ValidationException;

class InLineFileTest extends ValidationTestCase
{

    public function inLineFileProvider(): array
    {
        return [
            ['data:image/webp;charset=utf-8;base64,UklGRh4AAABXRUJQVlA4TBEAAAAvBAABAAdQjraXpP+BiOh/AAA=', true],
            ['data:image/webp;base64,UklGRh4AAABXRUJQVlA4TBEAAAAvBAABAAdQjraXpP+BiOh/AAA=', true],
            ['data:text/plain;base64,QQ==', true],
            ['data:text/plain;base64,', true],
            [null, false],
            ['', false],
            ['asd', false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider inLineFileProvider
     */
    public function testInLineFile(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new InLineFileRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'inlinefile']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }

}
