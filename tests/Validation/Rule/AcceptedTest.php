<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\AcceptedRule;
use TinyFramework\Validation\ValidationException;

class AcceptedTest extends ValidationTestCase
{

    public function acceptedProvider(): array
    {
        return [
            ['ON', true],
            ['On', true],
            ['on', true],
            ['yes', true],
            ['y', true],
            ['1', true],
            [1, true],
            ['true', true],
            [true, true],
            ['null', false],
            ['OFF', false],
            ['Off', false],
            ['off', false],
            ['no', false],
            ['n', false],
            ['0', false],
            [0, false],
            ['false', false],
            [false, false],
            [null, false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider acceptedProvider
     */
    public function testAccepted(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new AcceptedRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'accepted']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }

}
