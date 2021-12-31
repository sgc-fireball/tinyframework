<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Localization;

use DateTime;
use PHPUnit\Framework\TestCase;
use TinyFramework\Cron\CronExpression;
use TinyFramework\Localization\TranslationLoader;

class TranslationLoaderTest extends TestCase
{
    public function testLoader()
    {
        $locale = 'en';
        $translation = new TranslationLoader([__DIR__ . '/lang']);
        $translation->load($locale);
        $this->assertEquals('test1', $translation->get($locale, 'test.test1'));
        $this->assertEquals('test3', $translation->get($locale, 'test.test2.test3'));
        $this->assertEquals('test5', $translation->get($locale, 'test.test4.test5'));
        $this->assertEquals('test.unknown', $translation->get($locale, 'test.unknown'));
    }
}
