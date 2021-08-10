<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\PresentRule;
use TinyFramework\Validation\ValidationException;

class PresentTest extends ValidationTestCase
{

    public function testPresentMissing(): void
    {
        try {
            $this->validator->addRule(new PresentRule($this->translator));
            $this->validator->validate(
                [],
                ['field' => 'present']
            );
            $this->assertTrue(false);
        } catch (ValidationException $e) {
            $this->assertFalse(false);
        }
    }

    public function testPresent(): void
    {
        try {
            $this->validator->addRule(new PresentRule($this->translator));
            $this->validator->validate(
                ['field' => null],
                ['field' => 'present']
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->assertFalse(true);
        }
    }

}
