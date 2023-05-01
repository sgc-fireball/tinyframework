<?php

namespace TinyFramework\Validation\Rule;

/**
 * @link https://github.com/validatorjs/validator.js/blob/master/src/lib/isCreditCard.js
 */
class CreditCardRule extends RuleAwesome
{
    private array $providers = [
        'amex' => '/^3[47][0-9]{13}$/',
        'dinersclub' => '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
        'discover' => '/^6(?:011|5[0-9][0-9])[0-9]{12,15}$/',
        'jcb' => '/^(?:2131|1800|35\d{3})\d{11}$/',
        'mastercard' => '/^5[1-5][0-9]{2}|(222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/, // /^[25][1-7][0-9]{14}$/',
        'unionpay' => '/^(6[27][0-9]{14}|^(81[0-9]{14,17}))$/',
        'visa' => '/^(?:4[0-9]{12})(?:[0-9]{3,6})?$/',
        'all' => '/^(?:4[0-9]{12}(?:[0-9]{3,6})?|5[1-5][0-9]{14}|(222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}|6(?:011|5[0-9][0-9])[0-9]{12,15}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11}|6[27][0-9]{14}|^(81[0-9]{14,17}))$/',
    ];

    public function getName(): string
    {
        return 'creditcard';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $cc = $attributes[$name] ?? null;
        $sanitized = preg_replace('/[- ]/', '', $cc);
        if (count($parameters) === 0) {
            $parameters = array_keys($this->providers);
        }
        foreach ($parameters as $provider) {
            if (array_key_exists($provider, $this->providers)) {
                if (preg_match($this->providers[$provider], $cc)) {
                    return null;
                }
            }
        }
        if (isLuhnValid($sanitized)) {
            return null;
        }
        return [$this->translator->trans('validation.creditcard', ['attribute' => $this->getTransName($name)])];
    }
}
