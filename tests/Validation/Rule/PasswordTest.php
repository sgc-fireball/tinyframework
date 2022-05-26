<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\PasswordRule;
use TinyFramework\Validation\ValidationException;

class PasswordTest extends ValidationTestCase
{
    public function testPasswordToShort()
    {
        $errorBag = null;
        try {
            $this->validator->addRule(new PasswordRule($this->translator));
            $this->validator->validate(
                ['field' => 'aA0!'],
                ['field' => 'password']
            );
        } catch (ValidationException $e) {
            $errorBag = $e->getErrorBag();
        }
        $this->assertIsArray($errorBag);
        $this->assertArrayHasKey('field', $errorBag);
        $this->assertEquals(1, count($errorBag['field']));
    }

    public function testPasswordMissingUppercase()
    {
        $errorBag = null;
        try {
            $this->validator->addRule(new PasswordRule($this->translator));
            $this->validator->validate(
                ['field' => 'zaffqqf0!'],
                ['field' => 'password']
            );
        } catch (ValidationException $e) {
            $errorBag = $e->getErrorBag();
        }
        $this->assertIsArray($errorBag);
        $this->assertArrayHasKey('field', $errorBag);
        $this->assertEquals(1, count($errorBag['field']));
    }

    public function testPasswordMissingLowercase()
    {
        $errorBag = null;
        try {
            $this->validator->addRule(new PasswordRule($this->translator));
            $this->validator->validate(
                ['field' => 'FKFDKFW0!'],
                ['field' => 'password']
            );
        } catch (ValidationException $e) {
            $errorBag = $e->getErrorBag();
        }
        $this->assertIsArray($errorBag);
        $this->assertArrayHasKey('field', $errorBag);
        $this->assertEquals(1, count($errorBag['field']));
    }

    public function testPasswordMissingNumerics()
    {
        $errorBag = null;
        try {
            $this->validator->addRule(new PasswordRule($this->translator));
            $this->validator->validate(
                ['field' => 'FKasdKFW#!'],
                ['field' => 'password']
            );
        } catch (ValidationException $e) {
            $errorBag = $e->getErrorBag();
        }
        $this->assertIsArray($errorBag);
        $this->assertArrayHasKey('field', $errorBag);
        $this->assertEquals(1, count($errorBag['field']));
    }

    public function testPasswordMissingSymbols()
    {
        $errorBag = null;
        try {
            $this->validator->addRule(new PasswordRule($this->translator));
            $this->validator->validate(
                ['field' => 'adDKFW134'],
                ['field' => 'password']
            );
        } catch (ValidationException $e) {
            $errorBag = $e->getErrorBag();
        }
        $this->assertIsArray($errorBag);
        $this->assertArrayHasKey('field', $errorBag);
        $this->assertEquals(1, count($errorBag['field']));
    }

    public function testPasswordPwned()
    {
        if (!@dns_get_record('google.de')) {
            $this->markTestSkipped('Missing internet connection!');
        }

        $errorBag = null;
        try {
            $this->validator->addRule(new PasswordRule($this->translator));
            $this->validator->validate(
                ['field' => 'Passw0rd!'],
                ['field' => 'password']
            );
        } catch (ValidationException $e) {
            $errorBag = $e->getErrorBag();
        }
        $this->assertIsArray($errorBag);
        $this->assertArrayHasKey('field', $errorBag);
        $this->assertEquals(1, count($errorBag['field']));
    }
}
