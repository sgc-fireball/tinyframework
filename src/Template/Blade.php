<?php

declare(strict_types=1);

namespace TinyFramework\Template;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use TinyFramework\Cache\CacheInterface;
use TinyFramework\StopWatch\StopWatch;

class Blade implements ViewInterface
{
    private array $config;

    private array $placeholder = [];

    /** @var Closure[] */
    private array $directive = [];

    private array $footer = [];

    private array $sectionStack = [];

    private ?CacheInterface $cache = null;

    private StopWatch $stopWatch;

    private array $vendorDirectories = [];

    /** @var Closure[] */
    private array $preCompilers = [];

    /** @var Closure[] */
    private array $postCompilers = [];

    private array $shared = [];

    public function __construct(
        #[\SensitiveParameter] array $config,
        StopWatch $stopWatch = null,
        CacheInterface $cache = null
    ) {
        $this->config = $config;
        $this->config['cache'] = $this->config['cache'] ?? true;
        $this->config['source'] = $this->config['source'] ?? 'resources/views';
        $this->stopWatch = $stopWatch ?? new StopWatch();
        $this->cache = $this->config['cache'] ? $cache->tag('template') : null;
    }

    public function share(string $key, mixed $value): static
    {
        if ($value === null) {
            if (array_key_exists($key, $this->shared)) {
                unset($this->shared[$key]);
            }
            return $this;
        }
        $this->shared[$key] = $value;
        return $this;
    }

    public function addDirective(string $directive, Closure $callback): static
    {
        $this->directive[$directive] = $callback;
        return $this;
    }

    public function addNamespaceDirectory(string $namespace, string $dir): static
    {
        if (!is_dir($dir) || !is_readable($dir)) {
            throw new RuntimeException('Folder does not exists or is not readable: ' . $dir);
        }
        $this->vendorDirectories[$namespace] = $dir;
        return $this;
    }

    private function view2file(string $view): ?string
    {
        $key = 'view2file:' . str_replace('.', ':', $view);
        if ($this->config['cache'] && $this->cache?->has($key)) {
            return $this->cache->get($key);
        }
        foreach ($this->view2files($view) as $file) {
            if (file_exists($file) && is_readable($file)) {
                if ($this->config['cache']) {
                    $this->cache->set($key, $file);
                }
                return $file;
            }
        }
        if ($this->config['cache']) {
            $this->cache->set($key, null);
        }
        return null;
    }

    private function view2files(string $view): array
    {
        $namespace = null;
        if (str_contains($view, '@')) {
            [$namespace, $view] = explode('@', $view, 2);
        }
        $view = ltrim(str_replace('.', '/', $view), '/');
        if ($namespace === null) {
            $directories = [
                sprintf('%s/%s.blade.php', $this->config['source'], $view),
                sprintf('%s/%s.blade.php', __DIR__ . '/views', $view),
            ];
        } else {
            $directories = [
                sprintf('%s/vendor/%s/%s.blade.php', $this->config['source'], $namespace, $view),
            ];
            if (\array_key_exists($namespace, $this->vendorDirectories)) {
                $directories[] = sprintf('%s/%s.blade.php', $this->vendorDirectories[$namespace], $view);
            }
        }
        return $directories;
    }

    public function exists(string $view): bool
    {
        return (bool)$this->view2file($view);
    }

    public function render(string $view, array $data = [], array $parentData = []): string
    {
        $this->stopWatch->start('blade.render', 'blade');
        $this->placeholder = [];
        $content = $this->execute(
            $this->compileFile($view),
            array_merge($parentData, $data)
        );
        $this->stopWatch->stop('blade.render');
        return $content;
    }

    /**
     * @internal
     */
    public function __internalRender(string $view, array $data = [], array $parentData = []): string
    {
        return $this->execute(
            $this->compileFile($view),
            array_merge($parentData, $data)
        );
    }

    public function renderString(string $content, array $data = [], array $parentData = []): string
    {
        return $this->execute(
            $this->compileString($content),
            array_merge($parentData, $data)
        );
    }

    public function compileFile(string $view): string
    {
        $key = 'template:' . str_replace('.', ':', $view);
        if ($this->config['cache'] && $this->cache?->has($key)) {
            return $this->cache->get($key);
        }

        $file = $this->view2file($view);
        if (!$file) {
            throw new InvalidArgumentException('View does not exists or unreadable: ' . $view);
        }
        $content = trim($this->compileString((string)file_get_contents($file)));
        if ($this->config['cache']) {
            $this->cache?->set($key, $content);
        }
        return $content;
    }

    public function compileString(string $content): string
    {
        $this->stopWatch->start('blade.compile', 'blade');
        foreach ($this->preCompilers as $compiler) {
            $content = \call_user_func($compiler, $content);
        }

        $this->footer = [];
        $content = $this->compileVerbatim($content);

        $content = $this->compileComment($content);
        $content = $this->compileEcho($content);
        $content = $this->compilePhp($content);

        $content = (string)preg_replace_callback(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
            fn ($match) => $this->compileStatement($match),
            $content
        );

        $content = $this->restorePlaceholder($content);
        if (\count($this->footer)) {
            $content = ltrim($content, PHP_EOL) . PHP_EOL . implode(PHP_EOL, array_reverse($this->footer));
        }

        foreach ($this->postCompilers as $compiler) {
            $content = \call_user_func($compiler, $content);
        }

        $content = trim($content);
        $this->stopWatch->stop('blade.compile');
        return $content;
    }

    private function compileStatement(array $match): string
    {
        if (mb_substr($match[1], 0, 1) === '@') {
            return $match[0];
        }
        $match[1] = mb_strtolower($match[1]);
        if (\array_key_exists($match[1], $this->directive)) {
            return $this->directive[$match[1]]($match[3] ?? '');
        } elseif (method_exists($this, $method = 'compile' . ucfirst($match[1]))) {
            return $this->$method($match[3] ?? '');
        }
        throw new RuntimeException('Unknown blade command: ' . $match[1]);
    }

    public function addPreCompiler(Closure $compiler): static
    {
        $this->preCompilers[] = $compiler;
        return $this;
    }

    public function addPostCompiler(Closure $compiler): static
    {
        $this->postCompilers[] = $compiler;
        return $this;
    }

    public function execute(string $__content, array $__data = []): string
    {
        $this->stopWatch->start('blade.execute', 'blade');
        $__env = $this;
        $__data = array_merge($this->shared, $__data);
        extract($__data);
        unset($__data);
        ob_start();
        try {
            eval('unset($__content); ?>' . $__content . '<?php');
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $__content = trim((string)ob_get_clean());
        }
        $this->stopWatch->stop('blade.execute');
        return $__content;
    }

    public function resetPlaceholder(): self
    {
        $this->placeholder = [];
        return $this;
    }

    public function getPlaceholder(string $name, string $key = null): string
    {
        return sprintf('##placeholder-%s-%s', $name, $key ?? uniqid());
    }

    public function compileVerbatim(string $content): string
    {
        return (string)preg_replace_callback('/(?<!@)@verbatim(.*?)@endverbatim/s', function ($matches) {
            $id = $this->getPlaceholder('verbatim');
            $this->placeholder[$id] = $matches[1];
            return $id;
        }, $content);
    }

    public function restorePlaceholder(string $content): string
    {
        if (\count($this->placeholder)) {
            // @TODO Performance Impact: replace with preg_replace_callback
            // @link https://gist.githubusercontent.com/jeboehm/558c5f399ef8008daa244c2fa178bd6c/raw/3f7c21d74852bd546af13eb94dcf2b0bcacd1205/seo_replace_boost.patch
            $content = str_replace(array_keys($this->placeholder), array_values($this->placeholder), $content);
        }
        return $content;
    }

    public function compileComment(string $content): string
    {
        return preg_replace('/{{--(.*?)--}}/s', '', $content);
    }

    public function compileEcho(string $content): string
    {
        $content = str_replace(['{{', '}}'], ['<?php echo(e(', ')); ?>'], $content);
        return str_replace(['{!!', '!!}'], ['<?php echo(', '); ?>'], $content);
    }

    public function compilePhp(string $content): string
    {
        return (string)preg_replace_callback('/(?<!@)@php(.*?)@endphp/s', function ($matches) {
            return sprintf('<?php %s ?>', $matches[1]);
        }, $content);
    }

    public function compileProps(string $expression): string
    {
        return sprintf('<?php extract%s; ?>', $expression);
    }

    public function compileClass(string $expression): string
    {
        return sprintf('<?php echo implode(" ",array_keys(array_filter%s)); ?>', $expression);
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
        if (!empty($expression) && $expression !== '()') {
            return sprintf('<?php if %s { continue; } ?>', $expression);
        }
        return '<?php continue; ?>';
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
        // @TODO
        #$parts = explode(',', substr($expression, 1, -1));
        #$options = isset($parts[1]) ? trim($parts[1]) : JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        #$depth = isset($parts[2]) ? trim($parts[2]) : 512;
        return sprintf('<?php echo json_encode%s; ?>', $expression);
    }

    public function compileMethod(string $expression): string
    {
        $expression = preg_replace('/[^A-Z]/', '', strtoupper($expression));
        return sprintf('<input type="hidden" name="_method" value="%s">', $expression);
    }

    public function compileInclude(string $expression): string
    {
        $expression = mb_substr($expression, 1, -1);
        return sprintf('<?php echo $__env->__internalRender(%s, get_defined_vars()); ?>', $expression) . "\n";
    }

    public function compileUnset(string $expression): string
    {
        return sprintf('<?php unset%s; ?>', $expression);
    }

    public function compileExtends(string $expression): string
    {
        $expression = mb_substr($expression, 1, -1);
        $this->footer[] = sprintf('<?php echo $__env->__internalRender(%s, get_defined_vars()); ?>', $expression);
        return '';
    }

    public function compileSection(string $expression): string
    {
        $section = trim($expression, "('\")");
        $this->sectionStack[] = $section;
        return sprintf('<?php if($__env->startSection%s): ?>', $expression);
    }

    public function compileEndsection(string $expression): string
    {
        array_pop($this->sectionStack);
        return '<?php endif; $__env->stopSection(); ?>';
    }

    public function compileStop(string $expression): string
    {
        array_pop($this->sectionStack);
        return '<?php endif; $__env->stopSection(); ?>';
    }

    public function compileOverwrite(string $expression): string
    {
        array_pop($this->sectionStack);
        return '<?php endif; $__env->stopSection(true); ?>';
    }

    public function compilePrepend(string $expression): string
    {
        array_pop($this->sectionStack);
        return '<?php endif; $__env->prependSection(); ?>';
    }

    public function compileAppend(string $expression): string
    {
        array_pop($this->sectionStack);
        return '<?php endif; $__env->appendSection(); ?>';
    }

    public function compileShow(string $expression): string
    {
        return '<?php endif; echo $__env->yieldSection(); ?>';
    }

    public function compileYield(string $expression): string
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

    public function startSection(string $section, string $content = null): bool
    {
        if ($content !== null) {
            $this->extendSection($section, e($content));
            return true;
        }

        // @TODO needs to run section? ##placeholder-section-content
        /*$id = $this->getPlaceholder('section', $section);
        if (array_key_exists($id, $this->placeholder)) {
            if (strpos($this->placeholder[$id], $id) === false) {
                ob_start();
                $this->sectionStack[] = $section;
                return false;
            }
        }*/

        if (ob_start()) {
            $this->sectionStack[] = $section;
            return true;
        }
        return false;
    }

    public function extendSection(string $section, string $content = null): string
    {
        $id = $this->getPlaceholder('section', $section);
        if (\array_key_exists($id, $this->placeholder)) {
            $content = (string)str_replace($id, (string)$content, $this->placeholder[$id]);
        }
        $this->placeholder[$id] = $content;
        return $section;
    }

    public function stopSection(bool $overwrite = false): string
    {
        if (empty($this->sectionStack)) {
            throw new RuntimeException('Cannot end a section without first starting one.');
        }
        $section = array_pop($this->sectionStack);
        if ($overwrite) {
            $this->placeholder[$this->getPlaceholder('section', $section)] = (string)ob_get_clean();
        } else {
            $this->extendSection($section, (string)ob_get_clean());
        }
        return $section;
    }

    public function prependSection(): string
    {
        if (empty($this->sectionStack)) {
            throw new RuntimeException('Cannot end a section without first starting one.');
        }
        $section = array_pop($this->sectionStack);
        $id = $this->getPlaceholder('section', $section);
        if (\array_key_exists($id, $this->placeholder)) {
            $this->placeholder[$id] = (string)ob_get_clean() . $this->placeholder[$id];
        } else {
            $this->placeholder[$id] = (string)ob_get_clean();
        }
        return $section;
    }

    public function appendSection(): string
    {
        if (empty($this->sectionStack)) {
            throw new RuntimeException('Cannot end a section without first starting one.');
        }
        $section = array_pop($this->sectionStack);
        $id = $this->getPlaceholder('section', $section);
        if (\array_key_exists($id, $this->placeholder)) {
            $this->placeholder[$id] = $this->placeholder[$id] . (string)ob_get_clean();
        } else {
            $this->placeholder[$id] = (string)ob_get_clean();
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
        if (\array_key_exists($id, $this->placeholder)) {
            $sectionContent = $this->placeholder[$id];
        }
        return trim(str_replace($id, '', $sectionContent));
    }

    public function clear(): static
    {
        if ($this->config['cache']) {
            $this->cache?->clear();
        }
        return $this;
    }
}
