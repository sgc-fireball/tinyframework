<?php

declare(strict_types=1);

namespace TinyFramework\Http;

use TinyFramework\Validation\Rule\RuleInterface;
use TinyFramework\Validation\ValidationException;
use TinyFramework\Validation\ValidatorInterface;

abstract class RequestValidator
{

    private array $safe = [];

    private array $errorBag = [];

    public function __construct(
        private ValidatorInterface $validator,
        private Request $request
    ) {
    }

    /**
     * @return array<string, RuleInterface|array|string>
     */
    abstract public function rules(): array;

    public function validate(): bool
    {
        try {
            $this->safe = $this->validator->validate($this->request, $this->rules());
            return true;
        } catch (ValidationException $e) {
            $this->errorBag = $e->getErrorBag();
            return false;
        }
    }

    public function safe(): array
    {
        return $this->safe;
    }

    public function getErrorBag(): array
    {
        return $this->errorBag;
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->request, $name], $arguments);
    }

}
