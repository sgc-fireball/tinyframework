<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\Ipv4Rule;
use TinyFramework\Validation\ValidationException;

class Ipv4Test extends ValidationTestCase
{
    public function ipv4Provider(): array
    {
        return [
            ['0.0.0.0', true],
            ['255.255.255.255', true],
            ['256.256.256.256', false],
            ['a', false],
            [123123123123, false],

            ['::1', false],
            ['FFFF::', false],
            ['FFFF::FFFF', false],
            ['ffff::ffff', false],
            ['fff::g:ffff', false],

            ['', false],
            [null, false],
            [0, false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider ipv4Provider
     */
    public function testIp(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new Ipv4Rule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'ipv4']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }
}
