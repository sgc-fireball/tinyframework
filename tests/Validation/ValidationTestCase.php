<?php declare(strict_types=1);

namespace TinyFramework\Tests\Validation;

use PHPUnit\Framework\TestCase;
use TinyFramework\Localization\TranslationLoader;
use TinyFramework\Localization\Translator;
use TinyFramework\Localization\TranslatorInterface;
use TinyFramework\Validation\Rule\PasswordRule;
use TinyFramework\Validation\Validator;
use TinyFramework\Validation\ValidatorInterface;

class ValidationTestCase extends TestCase
{

    protected TranslatorInterface $translator;

    protected ValidatorInterface $validator;

    public function setUp(): void
    {
        $this->translator = new Translator(new TranslationLoader([]), 'en');
        $this->validator = new Validator($this->translator);
    }

}
