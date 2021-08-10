<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\Ipv6Rule;
use TinyFramework\Validation\ValidationException;

class Ipv6Test extends ValidationTestCase
{

    public function ipv6Provider(): array
    {
        return [
            ['0.0.0.0', false],
            ['255.255.255.255', false],
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
     * @dataProvider ipv6Provider
     */
    public function testIpv6(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new Ipv6Rule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'ipv6']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }

}
