<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation\Rule;

use TinyFramework\Tests\Validation\ValidationTestCase;
use TinyFramework\Validation\Rule\AcceptedRule;
use TinyFramework\Validation\Rule\SometimesRule;
use TinyFramework\Validation\ValidationException;

class SometimesTest extends ValidationTestCase
{

    public function testSometimesMissing(): void
    {
        try {
            $this->validator->addRule(new SometimesRule($this->translator));
            $this->validator->validate(
                [],
                ['field' => 'sometimes|next_rule_that_wont_called']
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->assertFalse(true, 'Invalid');
        }
    }

    public function testSometimesExistsButEmpty(): void
    {
        try {
            $this->validator->addRule(new SometimesRule($this->translator));
            $this->validator->addRule(new AcceptedRule($this->translator));
            $this->validator->validate(
                ['field' => 'y'],
                ['field' => 'sometimes|accepted']
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->assertFalse(true, 'Invalid');
        }
    }

}
