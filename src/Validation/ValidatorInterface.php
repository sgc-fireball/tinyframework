<?php

declare(strict_types=1);

namespace TinyFramework\Validation;

use Iterator;
use TinyFramework\Http\Request;
use TinyFramework\Validation\Rule\RuleInterface;

interface ValidatorInterface
{
    public function addRules(array $rules): ValidatorInterface;

    public function addRule(RuleInterface $rule): ValidatorInterface;

    public function validate(Iterator|Request|array $attributes, array $rules): array;
}
