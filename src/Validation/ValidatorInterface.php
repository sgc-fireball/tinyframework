<?php

declare(strict_types=1);

namespace TinyFramework\Validation;

use Iterator;
use TinyFramework\Http\RequestInterface;
use TinyFramework\Validation\Rule\RuleInterface;

interface ValidatorInterface
{
    public function addRules(array $rules): ValidatorInterface;

    public function addRule(RuleInterface $rule): ValidatorInterface;

    public function validate(Iterator|RequestInterface|array $attributes, array $rules): array;
}
