<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\UrlRule;
use TinyFramework\Validation\ValidationException;

class UrlTest extends ValidationTestCase
{
    public function urlProvider(): array
    {
        return [
            ['http://google.de', true],
            ['https://google.de', true],
            ['http://google.de:81', true],
            ['https://google.de:444', true],
            ['http://google.de/test', true],
            ['https://google.de/test', true],
            ['imap://google.de:81/test', true],
            ['imaps://google.de:444/test', true],
            ['protocol://user:pass@example.org:1/path/to/file?query=parameter', true],
            ['asd://127.0.0.1/', true],
            ['http://[::1]/', true],

            ['http://testasdadadasasdasdasdadadsd.de', false],
            ['https://testasdadadasasdasdasdadadsd.de', false],
            ['Asdasd', false],
            ['Asdasd.de', false],
            ['Asdasd.de:80', false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider urlProvider
     */
    public function testUrl(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new UrlRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'url']
            );
            $this->assertTrue($valid, var_export($value, true));
        } catch (ValidationException $e) {
            $this->assertFalse($valid, var_export($value, true));
        }
    }
}
