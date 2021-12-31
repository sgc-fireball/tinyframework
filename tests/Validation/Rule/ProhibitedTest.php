<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\ProhibitedRule;
use TinyFramework\Validation\ValidationException;

class ProhibitedTest extends ValidationTestCase
{
    public function testProhibitedMissing(): void
    {
        try {
            $this->validator->addRule(new ProhibitedRule($this->translator));
            $this->validator->validate(
                ['field' => null],
                ['field' => 'prohibited']
            );
            $this->assertTrue(false);
        } catch (ValidationException $e) {
            $this->assertFalse(false);
        }
    }

    public function testProhibited(): void
    {
        try {
            $this->validator->addRule(new ProhibitedRule($this->translator));
            $this->validator->validate(
                [],
                ['field' => 'prohibited']
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->assertFalse(true);
        }
    }
}
