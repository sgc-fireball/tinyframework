<?php declare(strict_types=1);

namespace TinyFramework\Session;

interface SessionInterface
{

    public function getId(): string;

    public function open(?string $id): SessionInterface;

    public function close(): SessionInterface;

    public function destroy(): SessionInterface;

    public function get(string $key): mixed;

    public function has(string $key): bool;

    public function set(string $key, mixed $value): SessionInterface;

    public function forget(string $key): SessionInterface;

    public function clear(): SessionInterface;

}
