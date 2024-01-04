<?php

declare(strict_types=1);

namespace TinyFramework\Http;

use TinyFramework\Validation\Rule\RuleInterface;
use TinyFramework\Validation\ValidationException;
use TinyFramework\Validation\ValidatorInterface;

abstract class RequestValidator extends Request
{

    protected ValidatorInterface $validator;

    private array $safe = [];

    private array $errorBag = [];

    public function __construct()
    {
        parent::__construct();
        $this->validator = container(ValidatorInterface::class);
    }

    /**
     * @return array<string, RuleInterface|array|string>
     */
    abstract public function rules(): array;

    public function validate(): bool
    {
        try {
            $this->safe = $this->validator->validate($this, $this->rules());
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

}
