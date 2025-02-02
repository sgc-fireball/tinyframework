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

            // ipv4
            ['test@168.119.154.157', 'email', false],
            ['test@168.119.154.157', 'email:dns', true],
            ['test@168.119.154.157', 'email:rfc', false],
            ['test@168.119.154.157', 'email:dns,tcp', true],

            // ipv6
            ['test@2a01:4f8:1c17:5020::1', 'email', false],
            ['test@2a01:4f8:1c17:5020::1', 'email:dns', true],
            ['test@2a01:4f8:1c17:5020::1', 'email:rfc', false],
            ['test@2a01:4f8:1c17:5020::1', 'email:dns,tcp', true],

            // root domain
            ['test@hrdns.de', 'email', true],
            ['test@hrdns.de', 'email:dns', true],
            ['test@hrdns.de', 'email:rfc', true],
            ['test@hrdns.de', 'email:dns,tcp,rfc', true],

            // domain without mx
            ['test@mail2.hrdns.de', 'email', true],
            ['test@mail2.hrdns.de', 'email:dns', true],
            ['test@mail2.hrdns.de', 'email:rfc', true],
            ['test@mail2.hrdns.de', 'email:dns,tcp,rfc', true],

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
        if (!@dns_get_record('google.de') && str_contains($rule, 'dns')) {
            $this->markTestSkipped('Missing internet connection!');
        }
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
