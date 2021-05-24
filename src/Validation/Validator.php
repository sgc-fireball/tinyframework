<?php declare(strict_types=1);

namespace TinyFramework\Validation;

use RuntimeException;
use TinyFramework\Http\Request;
use TinyFramework\Localization\TranslatorInterface;
use TinyFramework\Validation\Rule\RuleInterface;

class Validator implements ValidatorInterface
{

    /** @var RuleInterface[] */
    private array $rules = [];

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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

    public function validate(Request|array $attributes, array $rules): array
    {
        if ($attributes instanceof Request) {
            $attributes = array_merge([], $attributes->get(), $attributes->post(), $attributes->file());
        }
        $errorBag = [];
        $values = [];
        foreach ($rules as $field => $fieldRules) {
            if ($errors = $this->validateField($attributes, $field, $fieldRules)) {
                $errorBag[$field] = $errors;
            } else {
                if (array_key_exists($field, $attributes)) {
                    $values[$field] = $attributes[$field];
                }
            }
        }
        if (count($errorBag)) {
            $exception = new ValidationException('Invalid data given.');
            $exception->setErrorBag($errorBag);
            throw $exception;
        }
        return $values;
    }

    private function validateField(array $attributes, string $name, string|array $rules): array|null
    {
        $errorBag = [];
        $rules = is_array($rules) ? $rules : explode('|', $rules);
        foreach ($rules as $rule) {
            $parameters = [];
            if (str_contains($rule, ':')) {
                list($rule, $parameters) = explode(':', $rule, 2);
                $parameters = explode(',', $parameters);
            }
            if (!array_key_exists($rule, $this->rules)) {
                throw new RuntimeException('Invalid rule: ' . $rule);
            }

            array_unshift($parameters, $name); // prepend name
            array_unshift($parameters, $attributes); // prepend attributes
            $response = call_user_func_array([$this->rules[$rule], 'validate'], $parameters);
            if (is_array($response)) {
                $errorBag = array_merge($errorBag, $response);
                $errorBag = array_unique($errorBag);
            } elseif ($response) {
                break;
            }
        }
        return count($errorBag) ? $errorBag : null;
    }

}
