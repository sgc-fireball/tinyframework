<?php

declare(strict_types=1);

namespace TinyFramework\Validation;

class ValidationException extends \RuntimeException
{
    private array $errorBag = [];

    public function setErrorBag(array $errorBag): static
    {
        $this->errorBag = $errorBag;
        return $this;
    }

    public function getErrorBag(): array
    {
        return $this->errorBag;
    }
}
