<?php declare(strict_types=1);

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
                ['password' => 'aA0!'],
                ['password' => 'password']
            );
        } catch (ValidationException $e) {
            $errorBag = $e->getErrorBag();
        }
        $this->assertIsArray($errorBag);
        $this->assertArrayHasKey('password', $errorBag);
        $this->assertEquals(1, count($errorBag['password']));
    }

    public function testPasswordMissingUppercase()
    {
        $errorBag = null;
        try {
            $this->validator->addRule(new PasswordRule($this->translator));
            $this->validator->validate(
                ['password' => 'zaffqqf0!'],
                ['password' => 'password']
            );
        } catch (ValidationException $e) {
            $errorBag = $e->getErrorBag();
        }
        $this->assertIsArray($errorBag);
        $this->assertArrayHasKey('password', $errorBag);
        $this->assertEquals(1, count($errorBag['password']));
    }

    public function testPasswordMissingLowercase()
    {
        $errorBag = null;
        try {
            $this->validator->addRule(new PasswordRule($this->translator));
            $this->validator->validate(
                ['password' => 'FKFDKFW0!'],
                ['password' => 'password']
            );
        } catch (ValidationException $e) {
            $errorBag = $e->getErrorBag();
        }
        $this->assertIsArray($errorBag);
        $this->assertArrayHasKey('password', $errorBag);
        $this->assertEquals(1, count($errorBag['password']));
    }

    public function testPasswordMissingNumerics()
    {
        $errorBag = null;
        try {
            $this->validator->addRule(new PasswordRule($this->translator));
            $this->validator->validate(
                ['password' => 'FKasdKFW#!'],
                ['password' => 'password']
            );
        } catch (ValidationException $e) {
            $errorBag = $e->getErrorBag();
        }
        $this->assertIsArray($errorBag);
        $this->assertArrayHasKey('password', $errorBag);
        $this->assertEquals(1, count($errorBag['password']));
    }

    public function testPasswordMissingSymbols()
    {
        $errorBag = null;
        try {
            $this->validator->addRule(new PasswordRule($this->translator));
            $this->validator->validate(
                ['password' => 'adDKFW134'],
                ['password' => 'password']
            );
        } catch (ValidationException $e) {
            $errorBag = $e->getErrorBag();
        }
        $this->assertIsArray($errorBag);
        $this->assertArrayHasKey('password', $errorBag);
        $this->assertEquals(1, count($errorBag['password']));
    }

    public function testPasswordPwned()
    {
        $errorBag = null;
        try {
            $this->validator->addRule(new PasswordRule($this->translator));
            $this->validator->validate(
                ['password' => 'Passw0rd!'],
                ['password' => 'password']
            );
        } catch (ValidationException $e) {
            $errorBag = $e->getErrorBag();
        }
        $this->assertIsArray($errorBag);
        $this->assertArrayHasKey('password', $errorBag);
        $this->assertEquals(1, count($errorBag['password']));
    }

}
