<?php declare(strict_types=1);

namespace TinyFramework\Tests\Color;

use TinyFramework\Color\XTermConverter;

class XTermConverterTest extends \PHPUnit\Framework\TestCase
{

    private ?XTermConverter $converter = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new XTermConverter();
    }

    public function testMap(): void
    {
        $map = XTermConverter::getMap();
        $this->assertTrue(is_array($map));
        $this->assertTrue(count($map) > 0);
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        $result = [];
        foreach (XTermConverter::getMap() as $hex => $xterm) {
            $result[] = [(string)$hex, $xterm];
        }
        return $result;
    }

    /**
     * @dataProvider dataProvider
     */
    public function testXterm2Hex(string $hex, int $xterm): void
    {
        $this->assertEquals($hex, $this->converter->xterm2hex($xterm));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHex2Xterm(string $hex, int $xterm): void
    {
        $this->assertEquals($xterm, $this->converter->hex2xterm($hex));
    }

    public function test88ffff(): void
    {
        $this->assertEquals(123, $this->converter->hex2xterm('88ffff'));
    }

    public function dataXterm2HexProvider(): array
    {
        $result = [];
        $result[] = ['000000', 16];
        $result[] = ['0000ff', 21];
        $result[] = ['00ff00', 46];
        $result[] = ['00ffff', 51];
        $result[] = ['ff0000', 196];
        $result[] = ['ff00ff', 201];
        $result[] = ['ffff00', 226];
        $result[] = ['ffffff', 231];
        $result[] = ['808080', 244];
        return $result;
    }

    /**
     * @dataProvider dataXterm2HexProvider
     * @param string $hex
     * @param int $xterm
     */
    public function testXterm2Hex2(string $hex, int $xterm): void
    {
        $this->assertEquals($hex, $this->converter->xterm2hex($xterm));
    }

}
