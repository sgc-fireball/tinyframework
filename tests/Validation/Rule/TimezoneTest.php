<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\TimezoneRule;
use TinyFramework\Validation\ValidationException;

class TimezoneTest extends ValidationTestCase
{
    public function timezoneProvider(): array
    {
        return [
            [new \DateTimeZone('UTC'), true],
            ['Europe/Berlin', true],
            ['-02:00', true],
            ['+00:00', true],
            ['+02:00', true],
            ['CEST', true],
            ['GMT', true],
            ['UTC', true],
            ['EST', true],
            ['MDT', true],

            ['-0200', false],
            ['+0000', false],
            ['+0200', false],
            [-60, false],
            [0, false],
            [+60, false],
            ['Peter', false],
            ['AAA', false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $valid
     * @return void
     * @dataProvider timezoneProvider
     */
    public function testTimezone(mixed $value, bool $valid): void
    {
        try {
            $this->validator->addRule(new TimezoneRule($this->translator));
            $this->validator->validate(
                ['field' => $value],
                ['field' => 'timezone']
            );
            $this->assertTrue($valid);
        } catch (ValidationException $e) {
            $this->assertFalse($valid);
        }
    }
}
