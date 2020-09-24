<?php declare(strict_types=1);

namespace TinyFramework\Template;

use Closure;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use TinyFramework\Cache\CacheInterface;

class Blade implements ViewInterface
{

    public array $config;

    public array $placeholder = [];

    public array $footer = [];

    public array $sectionStack = [];

    public CacheInterface $cache;

    /** @var Closure[] */
    public array $preCompilers = [];

    /** @var Closure[] */
    public array $postCompilers = [];

    public function __construct(array $config, CacheInterface $cache)
    {
        $this->config = $config;
        $this->config['cache'] = $this->config['cache'] ?? true;
        $this->config['source'] = $this->config['source'] ?? 'resources/views';
        $this->cache = $cache->tag('template');
    }

    private function view2file(string $view): array
    {
        return [
            sprintf('%s/%s.blade.php', $this->config['source'], str_replace('.', '/', $view)),
            sprintf('%s/%s.blade.php', __DIR__ . '/views', str_replace('.', '/', $view)),
        ];
    }

    public function exists(string $view): bool
    {
        foreach ($this->view2file($view) as $file) {
            if (file_exists($file)) {
                return true;
            }
        }
        return false;
    }

    public function render(string $view, array $data, array $parentData = []): string
    {
        return $this->execute(
            $this->compileFile($view),
            array_merge($parentData, $data)
        );
    }

    public function renderString(string $content, array $data, array $parentData = []): string
    {
        return $this->execute(
            $this->compileString($content),
            array_merge($parentData, $data)
        );
    }

    public function compileFile(string $view): string
    {
        $key = 'template:' . str_replace('.', ':', $view);
        if ($this->config['cache'] && $this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $file = null;
        foreach ($this->view2file($view) as $tpl) {
            if (file_exists($tpl)) {
                $file = $tpl;
                break;
            }
        }
        if (is_null($file) || !file_exists($file)) {
            throw new InvalidArgumentException('View does not exists: ' . $view);
        }
        $content = trim($this->compileString(file_get_contents($file)));
        if ($this->config['cache']) {
            $this->cache->set($key, $content);
        }
        return $content;
    }

    public function compileString(string $content): string
    {
        foreach ($this->preCompilers as $compiler) {
            $content = call_user_func($compiler, $content);
        }

        $this->footer = [];
        $content = $this->compileVerbatim($content);

        $content = $this->compileComment($content);
        $content = $this->compileEcho($content);
        $content = $this->compilePhp($content);

        $content = preg_replace_callback(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
            function ($match) {
                if (substr($match[1], 0, 1) === '@') {
                    return $match[0];
                }
                $match[1] = strtolower($match[1]);
                if (method_exists($this, $method = 'compile' . ucfirst($match[1]))) {
                    return $this->$method($match[3] ?? '');
                }
                throw new \RuntimeException('Unknown blade command: ' . $match[1]);
            },
            $content
        );

        $content = $this->restorePlaceholder($content);
        if (count($this->footer) > 0) {
            $content = ltrim($content, PHP_EOL) . PHP_EOL . implode(PHP_EOL, array_reverse($this->footer));
        }

        foreach ($this->postCompilers as $compiler) {
            $content = call_user_func($compiler, $content);
        }

        return trim($content);
    }

    public function addPreCompiler(Closure $compiler): ViewInterface
    {
        $this->preCompilers[] = $compiler;
        return $this;
    }

    public function addPostCompiler(Closure $compiler): ViewInterface
    {
        $this->postCompilers[] = $compiler;
        return $this;
    }

    public function execute(string $__content, array $__data = []): string
    {
        $__env = $this;
        extract($__data);
        unset($__data);
        ob_start();
        try {
            eval('unset($__content); ?>' . $__content . '<?php');
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $__content = ob_get_clean();
        }
        return trim($__content) . PHP_EOL;
    }

    public function getPlaceholder(string $name, string $key = null): string
    {
        return sprintf('##placeholder-%s-%s', $name, $key ?? uniqid(''));
    }

    public function compileVerbatim(string $content): string
    {
        return preg_replace_callback('/(?<!@)@verbatim(.*?)@endverbatim/s', function ($matches) {
            $id = $this->getPlaceholder('verbatim');
            $this->placeholder[$id] = $matches[1];
            return $id;
        }, $content);
    }

    public function restorePlaceholder(string $content): string
    {
        if (count($this->placeholder)) {
            $content = str_replace(array_keys($this->placeholder), array_values($this->placeholder), $content);
        }
        return $content;
    }

    public function compileComment(string $content): string
    {
        return preg_replace('/\{\{--.*--\}\}/', '', $content);
    }

    public function compileEcho(string $content): string
    {
        $content = str_replace(['{{', '}}'], ['<?php echo(e(', ')); ?>'], $content);
        return str_replace(['{!!', '!!}'], ['<?php echo(', '); ?>'], $content);
    }

    public function compilePhp(string $content): string
    {
        return preg_replace_callback('/(?<!@)@php(.*?)@endphp/s', function ($matches) {
            return sprintf('<?php %s ?>', $matches[1]);
        }, $content);
    }

    public function compileIf(string $expression): string
    {
        return sprintf('<?php if %s: ?>', $expression);
    }

    public function compileIsset(string $expression): string
    {
        return sprintf('<?php if (isset%s): ?>', $expression);
    }

    public function compileEmpty(string $expression): string
    {
        return sprintf('<?php if (empty%s): ?>', $expression);
    }

    public function compileElseif(string $expression): string
    {
        return sprintf('<?php elseif %s: ?>', $expression);
    }

    public function compileElse(string $expression): string
    {
        return '<?php else: ?>';
    }

    public function compileEndif(string $expression): string
    {
        return '<?php endif; ?>';
    }

    public function compileSwitch(string $expression): string
    {
        return sprintf('<?php switch%s: ?>', $expression);
    }

    public function compileCase(string $expression): string
    {
        return sprintf('<?php case%s: ?>', $expression);
    }

    public function compileEndswitch(string $expression): string
    {
        return '<?php endswitch ?>';
    }

    public function compileInject(string $expression): string
    {
        [$variable, $service] = explode(',', preg_replace("/[\(\)\\\"\']/", '', $expression));
        return sprintf('<?php $%s = container("%s"); ?>', trim($variable), trim($service));
    }

    public function compileBreak(string $expression): string
    {
        if (!empty($expression)) {
            return sprintf('<?php if %s { break; } ?>', $expression);
        }
        return '<?php break; ?>';
    }

    public function compileDefault(string $expression): string
    {
        return '<?php default: ?>';
    }

    public function compileContinue(string $expression): string
    {
        if (!empty($expression)) {
            return sprintf('<?php if %s { continue; } ?>', $expression);
        }
        return '<?php continue; ?>';
    }

    public function compileDump(string $expression): string
    {
        return sprintf('<?php dump%s ?>', $expression);
    }

    public function compileDd(string $expression): string
    {
        return sprintf('<?php dd%s ?>', $expression);
    }

    public function compileWhile(string $expression): string
    {
        return sprintf('<?php while%s: ?>', $expression);
    }

    public function compileEndwhile(string $expression): string
    {
        return '<?php endwhile; ?>';
    }

    public function compileForeach(string $expression): string
    {
        return sprintf('<?php foreach%s: ?>', $expression);
    }

    public function compileEndforeach(string $expression): string
    {
        return '<?php endforeach; ?>';
    }

    public function compileFor(string $expression): string
    {
        return sprintf('<?php for%s: ?>', $expression);
    }

    public function compileEndfor(string $expression): string
    {
        return '<?php endfor; ?>';
    }

    public function compileJson(string $expression): string
    {
        return sprintf('<?php echo json_encode%s ?>', $expression);
    }

    public function compileMethod(string $expression): string
    {
        $expression = preg_replace('/[^A-Z]/', '', strtoupper($expression));
        return sprintf('<input type="hidden" name="_method" value="%s">', $expression);
    }

    public function compileInclude(string $expression): string
    {
        $expression = substr($expression, 1, -1);
        return sprintf('<?php echo $__env->render(%s, get_defined_vars()); ?>', $expression);
    }

    public function compileUnset(string $expression): string
    {
        return sprintf('<?php unset%s; ?>', $expression);
    }

    public function compileTrans(string $expression): string
    {
        return sprintf('<?php _%s; ?>', $expression);
    }

    public function compileExtends(string $expression): string
    {
        $expression = substr($expression, 1, -1);
        $this->footer[] = sprintf('<?php echo $__env->render(%s, get_defined_vars()); ?>', $expression);
        return '';
    }

    public function compileContent(string $expression): string
    {
        return sprintf('<?php echo $__env->startSection%s; ?>', $expression);
    }

    public function compileSection(string $expression): string
    {
        $section = trim($expression, "('\")");
        $this->sectionStack[] = $section;
        return sprintf('<?php echo $__env->startSection%s; ?>', $expression);
    }

    public function compileEndsection(string $expression): string
    {
        array_pop($this->sectionStack);
        return '<?php $__env->stopSection(); ?>';
    }

    public function compileStop(string $expression): string
    {
        array_pop($this->sectionStack);
        return '<?php $__env->stopSection(); ?>';
    }

    public function compileOverwrite(string $expression): string
    {
        array_pop($this->sectionStack);
        return '<?php $__env->stopSection(true); ?>';
    }

    public function compilePrepend(string $expression): string
    {
        array_pop($this->sectionStack);
        return '<?php $__env->prependSection(); ?>';
    }

    public function compileAppend(string $expression): string
    {
        array_pop($this->sectionStack);
        return '<?php $__env->appendSection(); ?>';
    }

    public function compileShow(string $expression): string
    {
        return '<?php echo $__env->yieldSection(); ?>';
    }

    protected function compileYield(string $expression): string
    {
        return sprintf('<?php echo $__env->yieldContent%s; ?>', $expression);
    }

    public function compileParent(): string
    {
        if (empty($this->sectionStack)) {
            return '';
        }
        $section = $this->sectionStack[count($this->sectionStack) - 1];
        return $this->getPlaceholder('section', $section);
    }

    public function startSection(string $section, string $content = null): void
    {
        if ($content === null) {
            if (ob_start()) {
                $this->sectionStack[] = $section;
            }
        } else {
            $this->extendSection($section, e($content));
        }
    }

    public function extendSection(string $section, string $content = null): string
    {
        $id = $this->getPlaceholder('section', $section);
        if (array_key_exists($id, $this->placeholder)) {
            $content = str_replace($id, $content, $this->placeholder[$id]);
        }
        $this->placeholder[$id] = $content;
        return $section;
    }

    public function stopSection(bool $overwrite = false): string
    {
        if (empty($this->sectionStack)) {
            throw new \RuntimeException('Cannot end a section without first starting one.');
        }
        $section = array_pop($this->sectionStack);
        if ($overwrite) {
            $this->placeholder[$this->getPlaceholder('section', $section)] = ob_get_clean();
        } else {
            $this->extendSection($section, ob_get_clean());
        }
        return $section;
    }

    public function prependSection(): string
    {
        if (empty($this->sectionStack)) {
            throw new \RuntimeException('Cannot end a section without first starting one.');
        }
        $section = array_pop($this->sectionStack);
        $id = $this->getPlaceholder('section', $section);
        if (array_key_exists($id, $this->placeholder)) {
            $this->placeholder[$id] = ob_get_clean() . $this->placeholder[$id];
        } else {
            $this->placeholder[$id] = ob_get_clean();
        }
        return $section;
    }

    public function appendSection(): string
    {
        if (empty($this->sectionStack)) {
            throw new \RuntimeException('Cannot end a section without first starting one.');
        }
        $section = array_pop($this->sectionStack);
        $id = $this->getPlaceholder('section', $section);
        if (array_key_exists($id, $this->placeholder)) {
            $this->placeholder[$id] = $this->placeholder[$id] . ob_get_clean();
        } else {
            $this->placeholder[$id] = ob_get_clean();
        }
        return $section;
    }

    public function yieldSection(): string
    {
        if (empty($this->sectionStack)) {
            return '<!-- empty sections -->';
        }
        return $this->yieldContent($this->stopSection());
    }

    public function yieldContent(string $section, string $sectionContent = ''): string
    {
        $sectionContent = e($sectionContent);
        $id = $this->getPlaceholder('section', $section);
        if (array_key_exists($id, $this->placeholder)) {
            $sectionContent = $this->placeholder[$id];
        }
        return trim(str_replace($id, '', $sectionContent));
    }

    public function clear(): ViewInterface
    {
        $this->cache->clear();
        return $this;
    }

}
