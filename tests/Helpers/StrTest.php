<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use TinyFramework\Helpers\Str;

class StrTest extends TestCase
{
    public function testToString(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testClone(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testSlug(): void
    {
        $str = Str::factory('Hello, world!')->slug();
        $this->assertInstanceOf(Str::class, $str);
        $this->assertEquals('hello-world', $str->string());

        $this->markTestSkipped('TODO');
        /*$str = Str::factory('äöüß')->slug();
        $this->assertInstanceOf(Str::class, $str);
        $this->assertEquals('aeoeuess', $str->string());*/
    }

    public function testKebabCase(): void
    {
        $str = Str::factory('Hello, World!')->kebabCase();
        $this->assertInstanceOf(Str::class, $str);
        $this->assertEquals('hello-world', $str->string());
    }

    public function testSnakeCase(): void
    {
        $str = Str::factory('Hello, World!')->snakeCase();
        $this->assertInstanceOf(Str::class, $str);
        $this->assertEquals('hello_world', $str->string());
    }

    public function testCamelCase(): void
    {
        $str = Str::factory('Hello, World!')->camelCase();
        $this->assertInstanceOf(Str::class, $str);
        $this->assertEquals('helloWorld', $str->string());
    }

    public function testLowerCase(): void
    {
        $str = Str::factory('Hello, World!')->lowerCase();
        $this->assertInstanceOf(Str::class, $str);
        $this->assertEquals('hello, world!', $str->string());
    }

    public function testUpperCase(): void
    {
        $str = Str::factory('Hello, World!')->upperCase();
        $this->assertInstanceOf(Str::class, $str);
        $this->assertEquals('HELLO, WORLD!', $str->string());
    }

    public function testAddCSlashes(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testAddSlashes(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testChunkSplit(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testSubstr(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testCountChars(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testSubstrReplace(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testWordCount(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testHtmlEntityDecode(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testHtmlEntityEncode(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testHtmlSpecialCharsDecode(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testHtmlSpecialCharsEncode(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testLcfirst(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testUcfirst(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testPrefix(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testPostfix(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testWrap(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testSquish(): void
    {
        $this->assertEquals('test test', Str::factory(' test test')->squish());
        $this->assertEquals('test test', Str::factory('test test ')->squish());
        $this->assertEquals('test test', Str::factory(' test test ')->squish());
        $this->assertEquals('test test', Str::factory(' test  test ')->squish());
        $this->assertEquals('test test', Str::factory('  test  test  ')->squish());
    }

    public function testTrim(): void
    {
        $this->assertEquals(' test ', Str::factory(' test ')->trim('#'));
        $this->assertEquals('test', Str::factory(' test')->trim());
        $this->assertEquals('test', Str::factory('test ')->trim());
        $this->assertEquals('test', Str::factory(' test ')->trim());
        $this->assertEquals('test', Str::factory('  test  ')->trim());
        $this->assertEquals('test  test', Str::factory('  test  test  ')->trim());
    }

    public function testLtrim(): void
    {
        $this->assertEquals(' test ', Str::factory(' test ')->ltrim('#'));
        $this->assertEquals('test', Str::factory(' test')->ltrim());
        $this->assertEquals('test ', Str::factory('test ')->ltrim());
        $this->assertEquals('test ', Str::factory(' test ')->ltrim());
        $this->assertEquals('test  ', Str::factory('  test  ')->ltrim());
        $this->assertEquals('test  test  ', Str::factory('  test  test  ')->ltrim());
    }

    public function testRtrim(): void
    {
        $this->assertEquals(' test ', Str::factory(' test ')->rtrim('#'));
        $this->assertEquals(' test', Str::factory(' test')->rtrim());
        $this->assertEquals('test', Str::factory('test ')->rtrim());
        $this->assertEquals(' test', Str::factory(' test ')->rtrim());
        $this->assertEquals('  test', Str::factory('  test  ')->rtrim());
        $this->assertEquals('  test  test', Str::factory('  test  test  ')->rtrim());
    }

    public function testPadLeft(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testPadBoth(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testPadRight(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testReplace(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testIreplace(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStrstr(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStristr(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStrtok(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStrtr(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testShuffle(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testRepeat(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testReverse(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testNl2br(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testQuotedPrintableDecode(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testQuotedPrintableEncode(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testQuotemeta(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStripTags(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStripCSlashes(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStripSlashes(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testWordwrap(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStrpbrk(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStrrchr(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testContains(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStartsWith(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testEndsWith(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStrnatcasecmp(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStrnatcmp(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStrncasecmp(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStrncmp(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testPosition(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testCompareCaseInsensitive(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testCompare(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStrcoll(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStrcspn(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testOrd(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testReversePosition(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testStrspn(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testCaseInsensitivePosition(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testCaseInsensitiveReversePosition(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testLength(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testSubstrCompare(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testSubstrCount(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testCsv2arr(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testSplit(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testExplode(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testParseStr(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testParseUrl(): void
    {
        $this->markTestSkipped('TODO');
    }

    public function testMask(): void
    {
        $this->markTestSkipped('TODO');
    }
}
