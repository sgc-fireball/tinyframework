<?php declare(strict_types=1);

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
        $this->blade = new Blade(['cache' => false, 'source' => sys_get_temp_dir()]);
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
        $this->assertEquals('<?php echo implode(" ",array_keys(array_filter(["true" => true, "false" => false]))); ?>', $php);
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

    // Switch
    // Case
    // EndswitchCase
    // Break
    // Default
    // Continue

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

    // Include
    // Unset
    // Extends
    // Content
    // Section
    // Endsection
    // Stop
    // Overwrite
    // Prepend
    // Append
    // Show
    // Yield
    // Parent

}
