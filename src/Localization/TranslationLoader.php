<?php declare(strict_types=1);

namespace TinyFramework\Localization;

class TranslationLoader
{

    private array $paths;

    private array $translations = [];

    public function __construct(array $paths = [])
    {
        $this->paths = $paths;
        $this->paths[] = __DIR__ . '/lang/';
        $this->paths[] = (defined('ROOT') ? ROOT : '.') . '/resources/lang/';
    }

    public function addPath(string $path): self
    {
        $this->paths[] = $path;
        return $this;
    }

    public function load(string $locale): self
    {
        if (array_key_exists($locale, $this->translations)) {
            return $this;
        }
        $this->translations[$locale] = [];
        foreach ($this->paths as $path) {
            $this->loadFolder($locale, $path);
        }
        return $this;
    }

    private function loadFolder(string $locale, string $path): self
    {
        if (!is_dir($path)) {
            return $this;
        }
        if (!is_dir($path . '/' . $locale)) {
            return $this;
        }
        foreach (glob($path . '/' . $locale . '/*.php') as $file) {
            $this->loadFile($locale, $file);
        }
        return $this;
    }

    private function loadFile(string $locale, string $file)
    {
        if (!is_file($file)) {
            return $this;
        }
        try {
            $module = str_replace('.php', '', basename($file));
            $trans = require_once($file);
            $trans = is_array($trans) ? $trans : [];
            $trans = array_flat($trans);
            foreach ($trans as $key => $value) {
                $this->translations[$locale][$module . '.' . $key] = $value;
            }
        } catch (\Throwable $e) {

        }
        return $this;
    }

    public function get($locale, $key): string
    {
        return $this->load($locale)->translations[$locale][$key] ?? $key;
    }

}
