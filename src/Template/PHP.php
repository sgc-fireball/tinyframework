<?php declare(strict_types=1);

namespace TinyFramework\Template;

use InvalidArgumentException;

class PHP implements ViewInterface
{

    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->config['source'] = $this->config['source'] ?? 'resources/views';
    }

    private function view2file(string $view): string
    {
        return sprintf('%s/%s.php', $this->config['source'], str_replace('.', '/', $view));
    }

    public function exists(string $view): bool
    {
        return file_exists($this->view2file($view));
    }

    public function render(string $view, array $data = [], array $parentData = []): string
    {
        $file = $this->view2file($view);
        if (!file_exists($file)) {
            throw new InvalidArgumentException('View does not exists: ' . $view);
        }
        return $this->executeFile($file, array_merge($parentData, $data));
    }

    private function executeFile(string $__template, array $__data): string
    {
        $__env = $this;
        extract($__data);
        unset($__data);
        ob_start();
        try {
            if (file_exists($__template)) {
                require($__template);
            }
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $content = (string)ob_get_clean();
        }
        return $content;
    }

    public function renderString(string $content, array $data = [], array $parentData = []): string
    {
        return $this->executeString($content, array_merge($parentData, $data));
    }

    private function executeString(string $__content, array $__data): string
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
            $content = (string)ob_get_clean();
        }
        return $content;
    }

    public function clear(): static
    {
        return $this;
    }

}
