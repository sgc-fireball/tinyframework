<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use TinyFramework\Helpers\GPS;

/**
 * @link https://coordinates-converter.com/en/decimal/51.389930,6.638880?karte=OpenStreetMap&zoom=8
 * Decimal degree: N 51.389933 E 6.638888
 * Degrees Minutes: N 51° 23.395800 E 6° 38.332800
 * Degrees Minutes Seconds: N 51° 23' 23.748 E 6° 38' 19.968
 */
class GPSTest extends TestCase
{

    public function testOne()
    {
        $gps = new GPS('51.389933 6.638883');
        $this->assertEquals(51.389933, $gps->getLatitude());
        $this->assertEquals(6.638883, $gps->getLongitude());
        $this->assertEquals('N 51° 23.395980 E 6° 38.332980', $gps->getGPS());
        $this->assertEquals('N 51.389933 E 6.638883', $gps->getWGS84Degree());
        $this->assertEquals('N 51° 23\' 23.759" E 6° 38\' 19.979"', $gps->getWGS84Seconds());
    }

    public function testTwo()
    {
        $gps = new GPS('N 51° 23.395800 E 6° 38.332800');
        $this->assertEquals(51.38993, $gps->getLatitude());
        $this->assertEquals(6.63888, $gps->getLongitude());
        $this->assertEquals('N 51° 23.395800 E 6° 38.332800', $gps->getGPS());
        $this->assertEquals('N 51.389930 E 6.638880', $gps->getWGS84Degree());
        $this->assertEquals('N 51° 23\' 23.748" E 6° 38\' 19.968"', $gps->getWGS84Seconds());
    }

    public function testThree()
    {
        $gps = new GPS('N 51° 23\' 23.759" E 6° 38\' 19.979"');
        $this->assertEquals(51.389933, $gps->getLatitude());
        $this->assertEquals(6.638883, $gps->getLongitude());
        $this->assertEquals('N 51° 23.395983 E 6° 38.332983', $gps->getGPS());
        $this->assertEquals('N 51.389933 E 6.638883', $gps->getWGS84Degree());
        $this->assertEquals('N 51° 23\' 23.759" E 6° 38\' 19.979"', $gps->getWGS84Seconds());
    }

    public function testDistance()
    {
        $gps1 = new GPS('51.39264552969704 6.59161149214419');
        $gps2 = new GPS('51.39360953877256 6.642594919487844');
        $this->assertEquals(3.54, $gps1->distance($gps2, 'k'));
        $this->assertEquals(2.2, $gps1->distance($gps2, 'm'));
        $this->assertEquals(1.91, $gps1->distance($gps2, 'n'));
    }

}
