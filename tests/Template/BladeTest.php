<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Template;

use PHPUnit\Framework\TestCase;
use TinyFramework\Cache\ArrayCache;
use TinyFramework\Localization\TranslationLoader;
use TinyFramework\Localization\Translator;
use TinyFramework\Localization\TranslatorInterface;
use TinyFramework\Template\Blade;
use TinyFramework\Validation\Rule\PasswordRule;
use TinyFramework\Validation\Validator;
use TinyFramework\Validation\ValidatorInterface;

class BladeTest extends TestCase
{
    protected Blade $blade;

    public function setUp(): void
    {
        $this->blade = new Blade([
            'cache' => false,
            'source' => __DIR__ . '/views',
        ]);
    }

    public function testVerbatim(): void
    {
        $tpl = '@verbatim @php test @endphp @endverbatim';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('@php test @endphp', $php);
        $this->assertEquals('@php test @endphp', $this->blade->renderString($tpl));
    }

    public function testComment(): void
    {
        $tpl = 'test{{-- test --}}test';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('testtest', $php);
        $this->assertEquals('testtest', $this->blade->renderString($tpl));
    }

    public function testEcho(): void
    {
        $tpl = '{{ $test }}';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php echo(e( $test )); ?>', $php);
        $this->assertEquals('test', $this->blade->renderString($tpl, ['test' => 'test']));
        $this->assertEquals('&lt;b&gt;test&lt;/b&gt;', $this->blade->renderString($tpl, ['test' => '<b>test</b>']));
    }

    public function testEchoRaw(): void
    {
        $tpl = '{!! $test !!}';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php echo( $test ); ?>', $php);
        $this->assertEquals('test', $this->blade->renderString($tpl, ['test' => 'test']));
        $this->assertEquals('<b>test</b>', $this->blade->renderString($tpl, ['test' => '<b>test</b>']));
    }

    public function testPhp(): void
    {
        $tpl = '@php echo "a"; @endphp';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php  echo "a";  ?>', $php);
        $this->assertEquals('a', $this->blade->renderString($tpl));
    }

    public function testClass(): void
    {
        $tpl = '@class(["true" => true, "false" => false])';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals(
            '<?php echo implode(" ",array_keys(array_filter(["true" => true, "false" => false]))); ?>',
            $php
        );
        $this->assertEquals('true', $this->blade->renderString($tpl));
    }

    public function testIf(): void
    {
        $tpl = '@if($test) true @endif';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php if ($test): ?> true <?php endif; ?>', $php);
        $this->assertEquals('true', $this->blade->renderString($tpl, ['test' => 1]));
        $this->assertEquals('', $this->blade->renderString($tpl, ['test' => 0]));
    }

    public function testIsset(): void
    {
        $tpl = '@isset($test) true @endif';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php if (isset($test)): ?> true <?php endif; ?>', $php);
        $this->assertEquals('true', $this->blade->renderString($tpl, ['test' => 1]));
        $this->assertEquals('', $this->blade->renderString($tpl, []));
    }

    public function testEmpty(): void
    {
        $tpl = '@empty($test) true @endif';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php if (empty($test)): ?> true <?php endif; ?>', $php);
        $this->assertEquals('true', $this->blade->renderString($tpl, ['test' => null]));
        $this->assertEquals('', $this->blade->renderString($tpl, ['test' => 'a']));
    }

    public function testIfElse(): void
    {
        $tpl = '@if($test) true @else false @endif';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php if ($test): ?> true <?php else: ?>false <?php endif; ?>', $php);
        $this->assertEquals('true', $this->blade->renderString($tpl, ['test' => 1]));
        $this->assertEquals('false', $this->blade->renderString($tpl, ['test' => 0]));
    }

    public function testSwitchCaseDefault(): void
    {
        $tpl = "@switch(\$test)\n";
        $tpl .= "@case(1) 1 @break\n";
        $tpl .= "@case(2) 2 @break\n";
        $tpl .= "@case(3) 3\n";
        $tpl .= "@default 4\n";
        $tpl .= "@endswitch\n";

        $php = '<?php switch($test): ?>' . "\n";
        $php .= '<?php case(1): ?> 1 <?php break; ?>' . "\n";
        $php .= '<?php case(2): ?> 2 <?php break; ?>' . "\n";
        $php .= '<?php case(3): ?> 3' . "\n";
        $php .= '<?php default: ?>4' . "\n";
        $php .= '<?php endswitch ?>';

        $this->assertEquals($php, $this->blade->compileString($tpl));
        $this->assertEquals('1', $this->blade->renderString($tpl, ['test' => 1]));
        $this->assertEquals('2', $this->blade->renderString($tpl, ['test' => 2]));
        $this->assertEquals("3\n4", $this->blade->renderString($tpl, ['test' => 3]));
        $this->assertEquals('4', $this->blade->renderString($tpl, ['test' => 4]));
    }

    public function testBreak(): void
    {
        $tpl = '@while($i--) @break($i < 3) {{ $i }} @endwhile';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals(
            '<?php while($i--): ?> <?php if ($i < 3) { break; } ?> <?php echo(e( $i )); ?> <?php endwhile; ?>',
            $php
        );
        $this->assertEquals('4   3', $this->blade->renderString($tpl, ['i' => 5]));
    }

    public function testContinueWithCondition(): void
    {
        $tpl = '@while($i--) @continue($i < 3) {{ $i }} @endwhile';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals(
            '<?php while($i--): ?> <?php if ($i < 3) { continue; } ?> <?php echo(e( $i )); ?> <?php endwhile; ?>',
            $php
        );
        $this->assertEquals('4   3', $this->blade->renderString($tpl, ['i' => 5]));
    }

    public function testContinueWithoutCondition(): void
    {
        $tpl = '@while($i--) @continue {{ $i }} @endwhile';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php while($i--): ?> <?php continue; ?><?php echo(e( $i )); ?> <?php endwhile; ?>', $php);
        $this->assertEquals('', $this->blade->renderString($tpl, ['i' => 5]));
    }

    public function testWhile(): void
    {
        $tpl = '@while($i--) {{ $i }} @endwhile';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php while($i--): ?> <?php echo(e( $i )); ?> <?php endwhile; ?>', $php);
        $this->assertEquals('1  0', $this->blade->renderString($tpl, ['i' => 2]));
    }

    public function testForeach(): void
    {
        $tpl = '@foreach($items as $item) {{ $item }} @endforeach';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php foreach($items as $item): ?> <?php echo(e( $item )); ?> <?php endforeach; ?>', $php);
        $this->assertEquals('0  1  2', $this->blade->renderString($tpl, ['items' => [0, 1, 2]]));
    }

    public function testFor(): void
    {
        $tpl = '@for($i=0;$i<3;$i++) {{ $i }} @endfor';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php for($i=0;$i<3;$i++): ?> <?php echo(e( $i )); ?> <?php endfor; ?>', $php);
        $this->assertEquals('0  1  2', $this->blade->renderString($tpl));
    }

    public function testJson(): void
    {
        $tpl = '@json($test)';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php echo json_encode($test); ?>', $php);
        $this->assertEquals('[1,2]', $this->blade->renderString($tpl, ['test' => [1, 2]]));
        $this->assertEquals('{"a":1}', $this->blade->renderString($tpl, ['test' => ['a' => 1]]));
    }

    public function testMethod(): void
    {
        $tpl = '@method("POST")';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<input type="hidden" name="_method" value="POST">', $php);
    }

    public function testUnset(): void
    {
        $tpl = '@unset($test) @isset($test) true @endif';
        $php = $this->blade->compileString($tpl);
        $this->assertEquals('<?php unset($test); ?> <?php if (isset($test)): ?> true <?php endif; ?>', $php);
        $this->assertEquals('', $this->blade->renderString($tpl, ['test' => 1]));
    }

    public function testSection(): void
    {
        $tpl = '@section("test") test1 @endsection @section("test") test2 @show';

        $php = '<?php if($__env->startSection("test")): ?>';
        $php .= ' test1 ';
        $php .= '<?php endif; $__env->stopSection(); ?>';
        $php .= '<?php if($__env->startSection("test")): ?>';
        $php .= ' test2 ';
        $php .= '<?php endif; echo $__env->yieldSection(); ?>';

        $this->assertEquals($php, $this->blade->compileString($tpl));
        $this->assertEquals('test1', $this->blade->renderString($tpl));
    }

    public function testOverwrite(): void
    {
        $tpl = '@section("test") test1 @endsection @section("test") test2 @overwrite @section("test") test3 @show';

        $php = '<?php if($__env->startSection("test")): ?>';
        $php .= ' test1 ';
        $php .= '<?php endif; $__env->stopSection(); ?>';
        $php .= '<?php if($__env->startSection("test")): ?>';
        $php .= ' test2 ';
        $php .= '<?php endif; $__env->stopSection(true); ?>';
        $php .= '<?php if($__env->startSection("test")): ?>';
        $php .= ' test3 ';
        $php .= '<?php endif; echo $__env->yieldSection(); ?>';

        $this->assertEquals($php, $this->blade->compileString($tpl));
        $this->assertEquals('test2', $this->blade->renderString($tpl));
    }

    public function testPrepend(): void
    {
        $tpl = '@section("test") test1 @endsection @section("test") prepend @prepend @section("test") test2 @show';

        $php = '<?php if($__env->startSection("test")): ?>';
        $php .= ' test1 ';
        $php .= '<?php endif; $__env->stopSection(); ?>';
        $php .= '<?php if($__env->startSection("test")): ?>';
        $php .= ' prepend ';
        $php .= '<?php endif; $__env->prependSection(); ?>';
        $php .= '<?php if($__env->startSection("test")): ?>';
        $php .= ' test2 ';
        $php .= '<?php endif; echo $__env->yieldSection(); ?>';

        $this->assertEquals($php, $this->blade->compileString($tpl));
        $this->assertEquals('prepend  test1', $this->blade->renderString($tpl));
    }

    public function testAppend(): void
    {
        $tpl = '@section("test") test1 @endsection @section("test") append @append @section("test") test2 @show';

        $php = '<?php if($__env->startSection("test")): ?>';
        $php .= ' test1 ';
        $php .= '<?php endif; $__env->stopSection(); ?>';
        $php .= '<?php if($__env->startSection("test")): ?>';
        $php .= ' append ';
        $php .= '<?php endif; $__env->appendSection(); ?>';
        $php .= '<?php if($__env->startSection("test")): ?>';
        $php .= ' test2 ';
        $php .= '<?php endif; echo $__env->yieldSection(); ?>';

        $this->assertEquals($php, $this->blade->compileString($tpl));
        $this->assertEquals('test1  append', $this->blade->renderString($tpl));
    }

    public function testShow(): void
    {
        $tpl = '@section("test") test @show';

        $php = '<?php if($__env->startSection("test")): ?>';
        $php .= ' test ';
        $php .= '<?php endif; echo $__env->yieldSection(); ?>';

        $this->assertEquals($php, $this->blade->compileString($tpl));
        $this->assertEquals('test', $this->blade->renderString($tpl));
    }

    public function testYield(): void
    {
        $tpl = '@section("test") test @endsection @section("test2") test2 @endsection @yield("test")';

        $php = '<?php if($__env->startSection("test")): ?>';
        $php .= ' test ';
        $php .= '<?php endif; $__env->stopSection(); ?>';
        $php .= '<?php if($__env->startSection("test2")): ?>';
        $php .= ' test2 ';
        $php .= '<?php endif; $__env->stopSection(); ?>';
        $php .= '<?php echo $__env->yieldContent("test"); ?>';

        $this->assertEquals($php, $this->blade->compileString($tpl));
        $this->assertEquals('test', $this->blade->renderString($tpl));
    }

    public function testParent(): void
    {
        $tpl = '@section("test") test @parent test @append @section("test") parent @show';

        $php = '<?php if($__env->startSection("test")): ?>';
        $php .= ' test ';
        $php .= '##placeholder-section-testtest ';
        $php .= '<?php endif; $__env->appendSection(); ?>';
        $php .= '<?php if($__env->startSection("test")): ?>';
        $php .= ' parent ';
        $php .= '<?php endif; echo $__env->yieldSection(); ?>';

        $this->assertEquals($php, $this->blade->compileString($tpl));
        $this->assertEquals('test  parent test', $this->blade->renderString($tpl));
    }

    public function testDirective(): void
    {
        $time = (string)microtime(true);
        $this->blade->addDirective('time', fn (string $expression) => $time);
        $this->assertEquals($time, $this->blade->compileString('@time'));
        $this->assertEquals($time, $this->blade->compileString('@time()'));
        $this->assertEquals($time, $this->blade->compileString('@time(123)'));
    }

    public function testInclude(): void
    {
        $this->assertEquals(
            "test1\ntest2\ntest4\ntest2\ntest1",
            $this->blade->render('include1', ['test' => 'test4'])
        );
    }

    public function testExtends(): void
    {
        $time = time();
        #$tpl1 = $this->blade->compileFile('extends1');
        #$tpl2 = $this->blade->compileFile('extends2');
        #echo $tpl1."\n".$tpl2."\n\n";
        $this->assertEquals(
            "test1\ntest4" . $time . "test4\ntest3",
            $this->blade->render('extends1', ['time' => $time])
        );
    }
}
