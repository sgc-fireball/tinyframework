<?php declare(strict_types=1);

namespace TinyFramework\Tests\Core;

use DateTime;
use PHPUnit\Framework\TestCase;
use TinyFramework\Core\DotEnv;

class DotEnvTest extends TestCase
{

    private ?DotEnv $dotEnv;

    public function setUp(): void
    {
        $this->dotEnv = DotEnv::instance();
    }

    public function testDotEnv(): void
    {
        $this->dotEnv->load(__DIR__ . '/inc/env');
        $this->assertEquals(null, $this->dotEnv->get('TEST_NULL1'));
        $this->assertEquals(null, $this->dotEnv->get('TEST_NULL2'));
        $this->assertEquals(null, $this->dotEnv->get('TEST_NULL3'));
        $this->assertEquals(null, $this->dotEnv->get('TEST_NULL4'));
        $this->assertEquals(false, $this->dotEnv->get('TEST_FALSE1'));
        $this->assertEquals(false, $this->dotEnv->get('TEST_FALSE2'));
        $this->assertEquals(true, $this->dotEnv->get('TEST_TRUE1'));
        $this->assertEquals(true, $this->dotEnv->get('TEST_TRUE2'));
        $this->assertEquals('test1', $this->dotEnv->get('TEST_STRING1'));
        $this->assertEquals('test2', $this->dotEnv->get('TEST_STRING2'));
        $this->assertEquals(1, $this->dotEnv->get('TEST_NUMBER1'));
        $this->assertEquals(1.23, $this->dotEnv->get('TEST_NUMBER2'));
        $this->assertEquals('aaa', $this->dotEnv->get('TEST_PLACEHOLDER3'));
        $this->assertEquals('aaa', $this->dotEnv->get('TEST_PLACEHOLDER2'));
        $this->assertEquals('testaaatest', $this->dotEnv->get('TEST_PLACEHOLDER1'));
        $this->assertEquals('testtest', $this->dotEnv->get('TEST_PLACEHOLDER4'));
    }

}
