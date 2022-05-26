<?php

declare(strict_types=1);

namespace TinyFramework\Validation;

use Iterator;
use RuntimeException;
use TinyFramework\Helpers\Arr;
use TinyFramework\Http\Request;
use TinyFramework\Validation\Rule\RuleInterface;

class Validator implements ValidatorInterface
{
    /** @var RuleInterface[] */
    private array $rules = [];

    public function addRules(array $rules): static
    {
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
        return $this;
    }

    public function addRule(RuleInterface $rule): static
    {
        $this->rules[$rule->getName()] = $rule;
        return $this;
    }

    public function validate(Iterator|Request|array $attributes, array $rules): array
    {
        if ($attributes instanceof Request) {
            $attributes = array_merge([], $attributes->get(), $attributes->post(), $attributes->file());
        }
        if ($attributes instanceof Iterator) {
            $attributes = iterator_to_array($attributes);
        }
        $errorBag = [];
        $values = [];

        $attributes = $this->flatDot($attributes);
        $rules = $this->expandRuleSet($attributes, $rules);
        foreach ($rules as $field => $fieldRules) {
            if ($errors = $this->validateField($attributes, $field, $fieldRules)) {
                $errorBag[$field] = $errors;
            } else {
                if (\array_key_exists($field, $attributes)) {
                    $values[$field] = $attributes[$field];
                }
            }
        }
        if (\count($errorBag)) {
            $exception = new ValidationException('Invalid data given.');
            $exception->setErrorBag($errorBag);
            throw $exception;
        }

        $values = array_filter($values, fn($value) => !is_array($value));
        $values = Arr::factory($values)->undot()->array();
        return $values;
    }

    private function validateField(array $attributes, string $name, string|array $rules): array|null
    {
        $errorBag = [];
        $rules = \is_array($rules) ? $rules : explode('|', $rules);
        foreach ($rules as $rule) {
            $parameters = [];
            if (str_contains($rule, ':')) {
                [$rule, $parameters] = explode(':', $rule, 2);
                $parameters = explode(',', $parameters);
            }
            if (!array_key_exists($rule, $this->rules)) {
                throw new RuntimeException('Invalid rule: ' . $rule);
            }

            array_unshift($parameters, $name); // prepend name
            array_unshift($parameters, $attributes); // prepend attributes
            $response = \call_user_func_array([$this->rules[$rule], 'validate'], $parameters);
            if (\is_array($response)) {
                $errorBag = array_merge($errorBag, $response);
                $errorBag = array_unique($errorBag);
            } elseif ($response) {
                break;
            }
        }
        return \count($errorBag) ? $errorBag : null;
    }

    protected function flatDot(array &$attributes, string $prepend = ''): array
    {
        $result = [];
        foreach ($attributes as $key => &$value) {
            $result[$prepend . $key] = $value;
            if (\is_array($value) && !empty($value)) {
                foreach ($this->flatDot($value, $prepend . $key . '.') as $sKey => &$sValue) {
                    $result[$sKey] = $sValue;
                }
            }
        }
        ksort($result);
        return $result;
    }

    protected function expandRuleSet(array $attributes, array $rules): array
    {
        $result = [];
        foreach ($rules as $key => $ruleSet) {
            if (!str_contains($key, '*')) {
                $result[$key] = $ruleSet;
                continue;
            }
            $pattern = explode('*', $key);
            $pattern = array_map(function ($part) {
                return preg_quote($part);
            }, $pattern);
            $pattern = implode('[^\.]+', $pattern);
            $pattern = '/^' . $pattern . '$/';
            foreach (array_keys($attributes) as $name) {
                if (preg_match($pattern, $name)) {
                    $result[$name] = $ruleSet;
                }
            }
        }
        ksort($result);
        return $result;
    }
}
