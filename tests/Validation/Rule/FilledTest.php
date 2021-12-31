<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\FilledRule;
use TinyFramework\Validation\ValidationException;

class FilledTest extends ValidationTestCase
{
    public function testFilledMissing(): void
    {
        try {
            $this->validator->addRule(new FilledRule($this->translator));
            $this->validator->validate(
                [],
                ['field' => 'filled']
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->assertFalse(true, 'Invalid');
        }
    }

    public function testFilledExistsButEmpty(): void
    {
        try {
            $this->validator->addRule(new FilledRule($this->translator));
            $this->validator->validate(
                ['field' => null],
                ['field' => 'filled']
            );
            $this->assertFalse(true, 'Invalid');
        } catch (ValidationException $e) {
            $this->assertTrue(true);
        }
    }

    public function testFilledExistsButEmptyArray(): void
    {
        try {
            $this->validator->addRule(new FilledRule($this->translator));
            $this->validator->validate(
                ['field' => []],
                ['field' => 'filled']
            );
            $this->assertFalse(true, 'Invalid');
        } catch (ValidationException $e) {
            $this->assertTrue(true);
        }
    }

    public function testFilledExistsButEmptyString(): void
    {
        try {
            $this->validator->addRule(new FilledRule($this->translator));
            $this->validator->validate(
                ['field' => ''],
                ['field' => 'filled']
            );
            $this->assertFalse(true, 'Invalid');
        } catch (ValidationException $e) {
            $this->assertTrue(true);
        }
    }

    public function testFilledExistsAndFilled(): void
    {
        try {
            $this->validator->addRule(new FilledRule($this->translator));
            $this->validator->validate(
                ['field' => 'Hallo'],
                ['field' => 'filled']
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->assertFalse(true, 'Invalid');
        }
    }
}
