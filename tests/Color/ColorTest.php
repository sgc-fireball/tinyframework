<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Color;

use PHPUnit\Framework\TestCase;
use TinyFramework\Color\Color;

class ColorTest extends TestCase
{
    private ?Color $colorConverter;

    protected function setUp(): void
    {
        $this->colorConverter = new Color();
    }

    public function dataProvider(): array
    {
        $data = [
            // xterm, cmyk, hsl, hsv, hex, rgb
            [0, [0, 0, 0, 1], [0, 0, 0], [0, 0, 0], '000000', [0, 0, 0]], // black
            [15, [0, 0, 0, 0], [0, 0, 100], [0, 0, 100], 'ffffff', [255, 255, 255]], // white
            [9, [0, 1, 1, 0], [0, 100, 50], [0, 100, 100], 'ff0000', [255, 0, 0]], // red
            [10, [1, 0, 1, 0], [120, 100, 50], [120, 100, 100], '00ff00', [0, 255, 0]], // lime
            [12, [1, 1, 0, 0], [240, 100, 50], [240, 100, 100], '0000ff', [0, 0, 255]], // blue
            [11, [0, 0, 1, 0], [60, 100, 50], [60, 100, 100], 'ffff00', [255, 255, 0]], // yellow
            [14, [1, 0, 0, 0], [180, 100, 50], [180, 100, 100], '00ffff', [0, 255, 255]], // cyan
            [13, [0, 1, 0, 0], [300, 100, 50], [300, 100, 100], 'ff00ff', [255, 0, 255]], // magenta
            [7, [0, 0, 0, 0.2471], [0, 0, 75.29], [0, 0, 75.29], 'c0c0c0', [192, 192, 192]], // silver
            [8, [0, 0, 0, 0.498], [0, 0, 50.2], [0, 0, 50.2], '808080', [128, 128, 128]], // gray
            [1, [0, 1, 1, 0.498], [0, 100, 25.1], [0, 100, 50.2], '800000', [128, 0, 0]], // maroon
            [3, [0, 0, 1, 0.498], [60, 100, 25.1], [60, 100, 50.2], '808000', [128, 128, 0]], // olive
            [2, [1, 0, 1, 0.498], [120, 100, 25.1], [120, 100, 50.2], '008000', [0, 128, 0]], // green
            [5, [0, 1, 0, 0.498], [300, 100, 25.1], [300, 100, 50.2], '800080', [128, 0, 128]], // purple
            [6, [1, 0, 0, 0.498], [180, 100, 25.1], [180, 100, 50.2], '008080', [0, 128, 128]], // teal
            [4, [1, 1, 0, 0.498], [240, 100, 25.1], [240, 100, 50.2], '000080', [0, 0, 128]], // navy
        ];

        return $data;
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_rgb2hsv(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hsv, $this->colorConverter->rgb2hsv($rgb[0], $rgb[1], $rgb[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_rgb2hsl(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hsl, $this->colorConverter->rgb2hsl($rgb[0], $rgb[1], $rgb[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_rgb2hex(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hex, $this->colorConverter->rgb2hex($rgb[0], $rgb[1], $rgb[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_rgb2cmyk(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($cmyk, $this->colorConverter->rgb2cmyk($rgb[0], $rgb[1], $rgb[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_rgb2xterm(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($xterm, $this->colorConverter->rgb2xterm($rgb[0], $rgb[1], $rgb[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hsv2rgb(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($rgb, $this->colorConverter->hsv2rgb($hsv[0], $hsv[1], $hsv[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hsv2hex(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hex, $this->colorConverter->hsv2hex($hsv[0], $hsv[1], $hsv[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hsv2hsl(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hsl, $this->colorConverter->hsv2hsl($hsv[0], $hsv[1], $hsv[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hsv2cmyk(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($cmyk, $this->colorConverter->hsv2cmyk($hsv[0], $hsv[1], $hsv[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hsl2rgb(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($rgb, $this->colorConverter->hsl2rgb($hsl[0], $hsl[1], $hsl[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hsl2hex(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hex, $this->colorConverter->hsl2hex($hsl[0], $hsl[1], $hsl[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hsl2cmyk(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($cmyk, $this->colorConverter->hsl2cmyk($hsl[0], $hsl[1], $hsl[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hsl2hsv(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hsv, $this->colorConverter->hsl2hsv($hsl[0], $hsl[1], $hsl[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hex2rgb(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($rgb, $this->colorConverter->hex2rgb($hex));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hex2hsl(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hsl, $this->colorConverter->hex2hsl($hex));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hex2cmyk(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($cmyk, $this->colorConverter->hex2cmyk($hex));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hex2hsv(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hsv, $this->colorConverter->hex2hsv($hex));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_cmyk2rgb(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($rgb, $this->colorConverter->cmyk2rgb($cmyk[0], $cmyk[1], $cmyk[2], $cmyk[3]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_cmyk2hsl(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hsl, $this->colorConverter->cmyk2hsl($cmyk[0], $cmyk[1], $cmyk[2], $cmyk[3]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_cmyk2hex(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hex, $this->colorConverter->cmyk2hex($cmyk[0], $cmyk[1], $cmyk[2], $cmyk[3]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_cmyk2hsv(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hsv, $this->colorConverter->cmyk2hsv($cmyk[0], $cmyk[1], $cmyk[2], $cmyk[3]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_xterm2cmyk(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($cmyk, $this->colorConverter->xterm2cmyk($xterm));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_cmyk2xterm(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($xterm, $this->colorConverter->cmyk2xterm($cmyk[0], $cmyk[1], $cmyk[2], $cmyk[3]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_xterm2hex(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hex, $this->colorConverter->xterm2hex($xterm));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hex2xterm(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($xterm, $this->colorConverter->hex2xterm($hex));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_xterm2hsl(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hsl, $this->colorConverter->xterm2hsl($xterm));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hsl2xterm(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($xterm, $this->colorConverter->hsl2xterm($hsl[0], $hsl[1], $hsl[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_xterm2hsv(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($hsv, $this->colorConverter->xterm2hsv($xterm));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_hsv2xterm(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($xterm, $this->colorConverter->hsv2xterm($hsv[0], $hsv[1], $hsv[2]));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_xterm2rgb(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $this->assertEquals($rgb, $this->colorConverter->xterm2rgb($xterm));
    }

    /**
     * @dataProvider dataProvider
     * @param integer $xterm
     * @param array $cmyk
     * @param array $hsl
     * @param array $hsv
     * @param string $hex
     * @param array $rgb
     */
    public function test_while(int $xterm, array $cmyk, array $hsl, array $hsv, string $hex, array $rgb): void
    {
        $_cmyk = $this->colorConverter->xterm2cmyk($xterm);
        $this->assertEquals($cmyk, $_cmyk);
        $_hsl = $this->colorConverter->cmyk2hsl($_cmyk[0], $_cmyk[1], $_cmyk[2], $_cmyk[3]);
        $this->assertEquals($hsl, $_hsl);
        $_hsv = $this->colorConverter->hsl2hsv($_hsl[0], $_hsl[1], $_hsl[2]);
        $this->assertEquals($hsv, $_hsv);
        $_hex = $this->colorConverter->hsv2hex($_hsv[0], $_hsv[1], $_hsv[2]);
        $this->assertEquals($hex, $_hex);
        $_rgb = $this->colorConverter->hex2rgb($_hex);
        $this->assertEquals($rgb, $_rgb);
        $_xterm = $this->colorConverter->rgb2xterm($_rgb[0], $_rgb[1], $_rgb[2]);
        $this->assertEquals($xterm, $_xterm);
    }

    public function testInvalidDataHex2Rgb(): void
    {
        $this->assertEquals([0, 0, 0], $this->colorConverter->hex2rgb('zzzzzz'));
    }

    public function dataMeetHueProvider(): array
    {
        $data = [
            // [r,g,b,x,y,bri=255]
            [255, 0, 0, 0.700606, 0.299301, 72], // red
            [0, 255, 0, 0.172416, 0.746797, 170], // green
            [0, 0, 255, 0.135503, 0.039879, 12], // blue

            // @see https://ninedegreesbelow.com/photography/xyz-rgb.html
            // @todo find a nicer way then to skip or comment out...
            //[255, 255, 255, 0.322727, 0.329023, 254], // white
            //[1, 1, 1, 0.322727, 0.329023, 0], // dark
            //[128, 128, 128, 0.322727, 0.329023, 55], // grey
        ];

        return $data;
    }

    /**
     * @see https://developers.meethue.com/documentation/core-concepts#color_gets_more_complicated
     * @see https://developers.meethue.com/wp-content/uploads/2018/02/color.png
     * @dataProvider dataMeetHueProvider
     */
    public function test_rgb2xyb(int $r, int $g, int $b, float $resultX, float $resultY, int $resultBri): void
    {
        [$x, $y, $bri] = $this->colorConverter->rgb2xyb($r, $g, $b);
        $this->assertEquals($resultX, $x);
        $this->assertEquals($resultY, $y);
        $this->assertEquals($resultBri, $bri);
    }

    /**
     * @see https://developers.meethue.com/documentation/core-concepts#color_gets_more_complicated
     * @dataProvider dataMeetHueProvider
     */
    public function test_xyb2rgb(int $resultR, int $resultG, int $resultB, float $x, float $y, int $resultBri): void
    {
        if ($resultR === $resultG && $resultG == $resultB) {
            $this->markTestSkipped('Could only test colors. Could not test brightness levels');
        }
        [$r, $g, $b] = $this->colorConverter->xyb2rgb($x, $y, $resultBri);
        $this->assertEquals($resultR, $r);
        $this->assertEquals($resultG, $g);
        $this->assertEquals($resultB, $b);
    }
}
