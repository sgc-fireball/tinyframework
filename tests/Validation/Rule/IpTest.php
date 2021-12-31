<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\IpRule;
use TinyFramework\Validation\ValidationException;

class IpTest extends ValidationTestCase
{
    public function ipProvider(): array
    {
        return [
            ['0.0.0.0', true],
            ['255.255.255.255', true],
            ['256.256.256.256', false],
            ['a', false],
            [123123123123, false],

            ['::1', true],
            ['FFFF::', true],
            ['FFFF::FFFF', true],
            ['ffff::ffff', true],
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
     * @dataProvider ipProvider
     */
    public function testIp(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new IpRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'ip']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }
}
