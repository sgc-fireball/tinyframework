<?php

namespace TinyFramework\Core;

trait TestKernelTrait
{
    public function handleException(\Throwable $e): int
    {
        self::$reservedMemory = null;
        throw $e;
    }

    protected function findServiceProviders(): void
    {
        parent::findServiceProviders();
        if (!file_exists('composer.json')) {
            return;
        }
        $content = file_get_contents('composer.json');
        if (!$content) {
            return;
        }
        $composer = json_decode($content, true);
        if (!is_array($composer)) {
            return;
        }
        if (!array_key_exists('extra', $composer)) {
            return;
        }
        if (!array_key_exists('tinyframework', $composer['extra'])) {
            return;
        }
        if (!array_key_exists('providers', $composer['extra']['tinyframework'])) {
            return;
        }
        if (!is_array($composer['extra']['tinyframework']['providers'])) {
            return;
        }
        foreach ($composer['extra']['tinyframework']['providers'] as $provider) {
            $this->serviceProviderNames[] = ltrim($provider, '\\');
        }
    }
}
