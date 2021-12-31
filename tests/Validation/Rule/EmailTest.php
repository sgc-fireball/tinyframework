<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Http\UploadedFile;
use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\BetweenRule;
use TinyFramework\Validation\Rule\EmailRule;
use TinyFramework\Validation\ValidationException;

class EmailTest extends ValidationTestCase
{
    public function emailProvider(): array
    {
        return [
            ['@testasdadadasasdasdasdadadsd.de', 'email', false],
            ['testasdadadasasdasdasdadadsd.de', 'email', false],

            ['test@testasdadadasasdasdasdadadsd.de', 'email', true],
            ['test@testasdadadasasdasdasdadadsd.de', 'email:rfc', true],
            ['test@testasdadadasasdasdasdadadsd.de', 'email:dns', false],

            ['test@github.com', 'email', true],
            ['test@github.com', 'email:dns,rfc', true],
            ['@@-///@@@@github.com', 'email:dns', true],
            ['@@-///@@@@github.com', 'email', false],
        ];
    }

    /**
     * @param mixed $value
     * @param string $rule
     * @param boolean $valid
     * @return void
     * @dataProvider emailProvider
     */
    public function testEmail(mixed $value, string $rule, bool $valid): void
    {
        try {
            $this->validator->addRule(new EmailRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => $rule]
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }
}
