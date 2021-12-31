<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Localization;

use DateTime;
use PHPUnit\Framework\TestCase;
use TinyFramework\Cron\CronExpression;
use TinyFramework\Localization\TranslationLoader;
use TinyFramework\Localization\Translator;

class TranslatorTest extends TestCase
{
    private ?Translator $translator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->translator = new Translator(
            new TranslationLoader([__DIR__ . '/lang']),
            'en'
        );
    }

    public function testTrans1()
    {
        $this->assertEquals('test.unknown', $this->translator->trans('test.unknown'));
    }

    public function testTrans2()
    {
        $this->assertEquals('test1', $this->translator->trans('test.test1'));
    }

    public function testTrans3()
    {
        $this->assertEquals('test3', $this->translator->trans('test.test2.test3'));
    }

    public function testTrans4()
    {
        $this->assertEquals('test5', $this->translator->trans('test.test4.test5'));
    }

    public function testVariable1()
    {
        $this->assertEquals('test one test', $this->translator->trans('test.test6', ['test' => 'one']));
    }

    public function testVariable2()
    {
        $this->assertEquals('test one test two test', $this->translator->trans('test.test7', ['test1' => 'one', 'test2' => 'two']));
    }

    public function testVariable3()
    {
        $this->assertEquals('test two test one test', $this->translator->trans('test.test8', ['test1' => 'one', 'test2' => 'two']));
    }

    public function testVariable4()
    {
        $this->assertEquals('test  1 test 1.230 test', $this->translator->trans('test.test9', ['test' => 1.23]));
    }

    public function testTransChoice1()
    {
        $this->assertEquals('zero', $this->translator->transChoice('test.numbers', 0));
    }

    public function testTransChoice2()
    {
        $this->assertEquals('one', $this->translator->transChoice('test.numbers', 1));
    }

    public function testTransChoice3()
    {
        $this->assertEquals('two', $this->translator->transChoice('test.numbers', 2));
    }

    public function testTransChoice4()
    {
        $this->assertEquals('more', $this->translator->transChoice('test.numbers', 3));
    }
}
