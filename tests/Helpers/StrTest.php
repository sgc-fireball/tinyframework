<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use TinyFramework\Helpers\Str;
use TinyFramework\Http\URL;

class StrTest extends TestCase
{
    public function testFactory()
    {
        $str = Str::factory('test');
        $this->assertInstanceOf(Str::class, $str);
        $this->assertEquals('test', $str->toString());
    }

    public function testChr()
    {
        $str = Str::chr(65);
        $this->assertEquals('A', $str->toString());
    }

    public function testHttpBuildQuery()
    {
        $data = ['foo' => 'bar', 'baz' => 'qux'];
        $str = Str::httpBuildQuery($data);
        $this->assertEquals('foo=bar&baz=qux', $str->toString());
    }

    public function testUuidMethods()
    {
        $uuid1 = Str::uuid1();
        $this->assertNotEmpty($uuid1->toString());

        $uuid4 = Str::uuid4();
        $this->assertNotEmpty($uuid4->toString());
    }

    public function testUild()
    {
        $uild = Str::uild();
        $this->assertNotEmpty($uild->toString());
    }

    public function testConstructor()
    {
        $str = new Str('test');
        $this->assertEquals('test', $str->toString());
    }

    public function testStringMethods()
    {
        $str = new Str('test');
        $this->assertEquals('test', $str->string());
        $this->assertEquals('test', $str->toString());
        $this->assertEquals('test', (string)$str);
    }

    public function testClone()
    {
        $str = new Str('test');
        $clone = $str->clone();
        $this->assertEquals($str->toString(), $clone->toString());
        $this->assertNotSame($str, $clone);
    }

    public function testSlug()
    {
        $str = new Str('Hello World!');
        $slug = $str->slug();
        $this->assertEquals('hello-world', $slug->toString());

        /*$str = new Str('äöüß');
        $slug = $str->slug();
        $this->assertEquals('aeoeuess', $slug->toString());*/
    }

    public function testKebabCase()
    {
        $str = new Str('Hello World');
        $kebab = $str->kebabCase();
        $this->assertEquals('hello-world', $kebab->toString());
    }

    public function testSnakeCase()
    {
        $str = new Str('Hello World');
        $snake = $str->snakeCase();
        $this->assertEquals('hello_world', $snake->toString());
    }

    public function testCamelCase()
    {
        $str = new Str('hello world');
        $camel = $str->camelCase();
        $this->assertEquals('helloWorld', $camel->toString());
    }

    public function testLowerCase()
    {
        $str = new Str('Hello World');
        $lower = $str->lowerCase();
        $this->assertEquals('hello world', $lower->toString());
    }

    public function testUpperCase()
    {
        $str = new Str('Hello World');
        $upper = $str->upperCase();
        $this->assertEquals('HELLO WORLD', $upper->toString());
    }

    public function testAddCSlashes()
    {
        $str = new Str('Hello World');
        $str = $str->addCSlashes('A..z');
        $this->assertEquals('\H\e\l\l\o \W\o\r\l\d', $str->toString());
    }

    public function testAddSlashes()
    {
        $str = new Str("Hello 'World'");
        $str = $str->addSlashes();
        $this->assertEquals("Hello \\'World\\'", $str->toString());
    }

    public function testChunkSplit()
    {
        $str = new Str('Hello World');
        $str = $str->chunkSplit(5, '-');
        $this->assertEquals('Hello- Worl-d-', $str->toString());
    }

    public function testSubstr()
    {
        $str = new Str('Hello World');
        $str = $str->substr(6);
        $this->assertEquals('World', $str->toString());
    }

    public function testCountChars()
    {
        $str = new Str('Hello');
        $result = $str->countChars(1);
        $this->assertIsArray($result->toArray());
    }

    public function testSubstrReplace()
    {
        $str = new Str('Hello World');
        $str = $str->substrReplace('Earth', 6);
        $this->assertEquals('Hello Earth', $str->toString());
    }

    public function testWordCount()
    {
        $str = new Str('Hello World');
        $count = $str->wordCount();
        $this->assertEquals(2, $count);
    }

    public function testHtmlEntityDecode()
    {
        $str = new Str('&lt;Hello&gt;');
        $str = $str->htmlEntityDecode();
        $this->assertEquals('<Hello>', $str->toString());
    }

    public function testHtmlEntityEncode()
    {
        $str = new Str('<Hello>');
        $str = $str->htmlEntityEncode();
        $this->assertEquals('&lt;Hello&gt;', $str->toString());
    }

    public function testHtmlSpecialCharsDecode()
    {
        $str = new Str('&lt;Hello&gt;');
        $str = $str->htmlSpecialCharsDecode();
        $this->assertEquals('<Hello>', $str->toString());
    }

    public function testHtmlSpecialCharsEncode()
    {
        $str = new Str('<Hello>');
        $str = $str->htmlSpecialCharsEncode();
        $this->assertEquals('&lt;Hello&gt;', $str->toString());
    }

    public function testLcfirst()
    {
        $str = new Str('Hello World');
        $str = $str->lcfirst();
        $this->assertEquals('hello World', $str->toString());
    }

    public function testUcfirst()
    {
        $str = new Str('hello world');
        $str = $str->ucfirst();
        $this->assertEquals('Hello world', $str->toString());
    }

    public function testPrefix()
    {
        $str = new Str('World');
        $str = $str->prefix('Hello ');
        $this->assertEquals('Hello World', $str->toString());
    }

    public function testPostfix()
    {
        $str = new Str('Hello');
        $str = $str->postfix(' World');
        $this->assertEquals('Hello World', $str->toString());
    }

    public function testWrap()
    {
        $str = new Str('Hello');
        $str = $str->wrap('<', '>');
        $this->assertEquals('<Hello>', $str->toString());
    }

    public function testSquish()
    {
        $str = new Str('  Hello   World  ');
        $str = $str->squish();
        $this->assertEquals('Hello World', $str->toString());
    }

    public function testTrim()
    {
        $str = new Str('  Hello World  ');
        $str = $str->trim();
        $this->assertEquals('Hello World', $str->toString());
    }

    public function testLtrim()
    {
        $str = new Str('  Hello World  ');
        $str = $str->ltrim();
        $this->assertEquals('Hello World  ', $str->toString());
    }

    public function testRtrim()
    {
        $str = new Str('  Hello World  ');
        $str = $str->rtrim();
        $this->assertEquals('  Hello World', $str->toString());
    }

    public function testPadLeft()
    {
        $str = new Str('Hello');
        $str = $str->padLeft(10, '-');
        $this->assertEquals('-----Hello', $str->toString());
    }

    public function testPadBoth()
    {
        $str = new Str('Hello');
        $str = $str->padBoth(10, '-');
        $this->assertEquals('--Hello---', $str->toString());
    }

    public function testPadRight()
    {
        $str = new Str('Hello');
        $str = $str->padRight(10, '-');
        $this->assertEquals('Hello-----', $str->toString());
    }

    public function testReplace()
    {
        $str = new Str('Hello World');
        $str = $str->replace('World', 'Earth');
        $this->assertEquals('Hello Earth', $str->toString());
    }

    public function testIreplace()
    {
        $str = new Str('Hello World');
        $str = $str->ireplace('world', 'Earth');
        $this->assertEquals('Hello Earth', $str->toString());
    }

    public function testStrstr()
    {
        $str = new Str('Hello World');
        $str = $str->strstr('World');
        $this->assertEquals('World', $str->toString());
    }

    public function testStristr()
    {
        $str = new Str('Hello World');
        $str = $str->stristr('world');
        $this->assertEquals('World', $str->toString());
    }

    public function testStrtok()
    {
        $str = new Str('Hello World');
        $str = $str->strtok(' ');
        $this->assertEquals('Hello', $str->toString());
    }

    public function testStrtr()
    {
        $str = new Str('Hello World');
        $str = $str->strtr('eo', '12');
        $this->assertEquals('H1ll2 W2rld', $str->toString());
    }

    public function testShuffle()
    {
        $str = new Str('Hello World');
        $str = $str->shuffle();
        $this->assertNotEquals('Hello World', $str->toString());
    }

    public function testRepeat()
    {
        $str = new Str('Hello');
        $str = $str->repeat(3);
        $this->assertEquals('HelloHelloHello', $str->toString());
    }

    public function testReverse()
    {
        $str = new Str('Hello');
        $str = $str->reverse();
        $this->assertEquals('olleH', $str->toString());
    }

    public function testNl2br()
    {
        $str = new Str("Hello\nWorld");
        $str = $str->nl2br();
        $this->assertEquals("Hello<br />\nWorld", $str->toString());
    }

    public function testQuotedPrintableDecode()
    {
        $str = new Str('Hello=20World');
        $str = $str->quotedPrintableDecode();
        $this->assertEquals('Hello World', $str->toString());
    }

    public function testQuotedPrintableEncode()
    {
        $str = new Str('Hello World==');
        $str = $str->quotedPrintableEncode();
        $this->assertEquals('Hello World=3D=3D', $str->toString());
    }

    public function testQuotemeta()
    {
        $str = new Str('Hello.World');
        $str = $str->quotemeta();
        $this->assertEquals('Hello\.World', $str->toString());
    }

    public function testStripTags()
    {
        $str = new Str('<p>Hello World</p>');
        $str = $str->stripTags();
        $this->assertEquals('Hello World', $str->toString());
    }

    public function testStripCSlashes()
    {
        $str = new Str('\Hello\World');
        $str = $str->stripCSlashes();
        $this->assertEquals('HelloWorld', $str->toString());
    }

    public function testStripSlashes()
    {
        $str = new Str('Hello\\World');
        $str = $str->stripSlashes();
        $this->assertEquals('HelloWorld', $str->toString());
    }

    public function testWordwrap()
    {
        $str = new Str('Hello World');
        $str = $str->wordwrap(5);
        $this->assertEquals("Hello\nWorld", $str->toString());
    }

    public function testStrpbrk()
    {
        $str = new Str('Hello World');
        $str = $str->strpbrk('o');
        $this->assertEquals('o World', $str->toString());
    }

    public function testStrrchr()
    {
        $str = new Str('Hello World');
        $str = $str->strrchr('o');
        $this->assertEquals('orld', $str->toString());
    }

    public function testContains()
    {
        $str = new Str('Hello World');
        $this->assertTrue($str->contains('World'));
    }

    public function testStartsWith()
    {
        $str = new Str('Hello World');
        $this->assertTrue($str->startsWith('Hello'));
    }

    public function testEndsWith()
    {
        $str = new Str('Hello World');
        $this->assertTrue($str->endsWith('World'));
    }

    public function testStrnatcasecmp()
    {
        $str = new Str('Hello');
        $this->assertEquals(0, $str->strnatcasecmp('hello'));
    }

    public function testStrnatcmp()
    {
        $str = new Str('Hello');
        $this->assertEquals(0, $str->strnatcmp('Hello'));
    }

    public function testStrncasecmp()
    {
        $str = new Str('Hello');
        $this->assertEquals(0, $str->strncasecmp('hello', 5));
    }

    public function testStrncmp()
    {
        $str = new Str('Hello');
        $this->assertEquals(0, $str->strncmp('Hello', 5));
    }

    public function testPosition()
    {
        $str = new Str('Hello World');
        $this->assertEquals(6, $str->position('World'));
    }

    public function testCompareCaseInsensitive()
    {
        $str = new Str('Hello');
        $this->assertEquals(0, $str->compareCaseInsensitive('hello'));
    }

    public function testCompare()
    {
        $str = new Str('Hello');
        $this->assertEquals(0, $str->compare('Hello'));
    }

    public function testStrcoll()
    {
        $str = new Str('Hello');
        $this->assertEquals(0, $str->strcoll('Hello'));
    }

    public function testStrcspn()
    {
        $str = new Str('Hello');
        $this->assertEquals(2, $str->strcspn('l'));
    }

    public function testOrd()
    {
        $str = new Str('A');
        $this->assertEquals(65, $str->ord());
    }

    public function testReversePosition()
    {
        $str = new Str('Hello World');
        $this->assertEquals(7, $str->reversePosition('o'));
    }

    public function testStrspn()
    {
        $str = new Str('Hello');
        $this->assertEquals(5, $str->strspn('Helo'));
    }

    public function testCaseInsensitivePosition()
    {
        $str = new Str('Hello World');
        $this->assertEquals(4, $str->caseInsensitivePosition('O'));
    }

    public function testCaseInsensitiveReversePosition()
    {
        $str = new Str('Hello World');
        $this->assertEquals(7, $str->caseInsensitiveReversePosition('O'));
    }

    public function testLength()
    {
        $str = new Str('Hello');
        $this->assertEquals(5, $str->length());
    }

    public function testSubstrCompare()
    {
        $str = new Str('Hello World');
        $this->assertEquals(0, $str->substrCompare('World', 6));
    }

    public function testSubstrCount()
    {
        $str = new Str('Hello World');
        $this->assertEquals(1, $str->substrCount('World'));
    }

    public function testCsv2arr()
    {
        $str = new Str('Hello,World');
        $arr = $str->csv2arr();
        $this->assertEquals(['Hello', 'World'], $arr->toArray());
    }

    public function testSplit()
    {
        $str = new Str('Hello');
        $arr = $str->split(2);
        $this->assertEquals(['He', 'll', 'o'], $arr->toArray());
    }

    public function testExplode()
    {
        $str = new Str('Hello World');
        $arr = $str->explode(' ');
        $this->assertEquals(['Hello', 'World'], $arr->toArray());
    }

    public function testParseStr()
    {
        $str = new Str('foo=bar&baz=qux');
        $arr = $str->parseStr();
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $arr->toArray());
    }

    public function testParseUrl()
    {
        $str = new Str('https://example.com');
        $url = $str->parseUrl();
        $this->assertInstanceOf(URL::class, $url);
    }

    public function testMask()
    {
        $str = new Str('Hello World');
        $str = $str->mask('*', 6, 5);
        $this->assertEquals('Hello *****', $str->toString());
    }
}
