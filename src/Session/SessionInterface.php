<?php declare(strict_types=1);

namespace TinyFramework\Session;

interface SessionInterface
{

    public function getId(): string;

    public function open(?string $id): SessionInterface;

    public function close(): SessionInterface;

    public function destroy(): SessionInterface;

    public function get(string $key, $default = null);

    public function has(string $key): bool;

    public function set(string $key, $value): SessionInterface;

    public function forget(string $key): SessionInterface;

    public function clear(): SessionInterface;

}
