<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Validation\Rule\AcceptedRule;
use TinyFramework\Validation\Rule\ArrayRule;
use TinyFramework\Validation\Rule\BetweenRule;
use TinyFramework\Validation\Rule\BooleanRule;
use TinyFramework\Validation\Rule\ConfirmedRule;
use TinyFramework\Validation\Rule\EmailRule;
use TinyFramework\Validation\Rule\FileRule;
use TinyFramework\Validation\Rule\FilledRule;
use TinyFramework\Validation\Rule\FloatRule;
use TinyFramework\Validation\Rule\ImageRule;
use TinyFramework\Validation\Rule\InRule;
use TinyFramework\Validation\Rule\IntegerRule;
use TinyFramework\Validation\Rule\IpRule;
use TinyFramework\Validation\Rule\Ipv4Rule;
use TinyFramework\Validation\Rule\Ipv6Rule;
use TinyFramework\Validation\Rule\JsonRule;
use TinyFramework\Validation\Rule\MaxRule;
use TinyFramework\Validation\Rule\MimetypesRule;
use TinyFramework\Validation\Rule\MinRule;
use TinyFramework\Validation\Rule\NotInRule;
use TinyFramework\Validation\Rule\NullableRule;
use TinyFramework\Validation\Rule\NumericRule;
use TinyFramework\Validation\Rule\PasswordRule;
use TinyFramework\Validation\Rule\PresentRule;
use TinyFramework\Validation\Rule\ProhibitedRule;
use TinyFramework\Validation\Rule\RequiredRule;
use TinyFramework\Validation\Rule\SometimesRule;
use TinyFramework\Validation\Rule\StringRule;
use TinyFramework\Validation\Rule\TimezoneRule;
use TinyFramework\Validation\Rule\UrlRule;
use TinyFramework\Validation\Rule\VideoRule;
use TinyFramework\Validation\Validator;
use TinyFramework\Validation\ValidatorInterface;

class ValidationServiceProvider extends ServiceProviderAwesome
{
    public function register(): void
    {
        $this->container->tag(['validators'], [
            AcceptedRule::class,
            ArrayRule::class,
            BetweenRule::class,
            BooleanRule::class,
            ConfirmedRule::class,
            EmailRule::class,
            FileRule::class,
            FilledRule::class,
            FloatRule::class,
            ImageRule::class,
            InRule::class,
            IntegerRule::class,
            IpRule::class,
            Ipv4Rule::class,
            Ipv6Rule::class,
            JsonRule::class,
            MaxRule::class,
            MimetypesRule::class,
            MinRule::class,
            NotInRule::class,
            NullableRule::class,
            NumericRule::class,
            PasswordRule::class,
            PresentRule::class,
            ProhibitedRule::class,
            RequiredRule::class,
            SometimesRule::class,
            StringRule::class,
            TimezoneRule::class,
            UrlRule::class,
            VideoRule::class,
        ]);
        $this->container
            ->alias('validator', ValidatorInterface::class)
            ->alias(ValidatorInterface::class, Validator::class)
            ->singleton(Validator::class, function () {
                $validator = new Validator();
                $validator->addRules($this->container->tagged('validators'));
                return $validator;
            });
    }
}
