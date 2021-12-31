<?php

declare(strict_types=1);

namespace TinyFramework\Broadcast;

use Closure;

class BroadcastManager
{
    private array $channels = [];

    private array $pattern = ['default' => '[^\.]+'];

    private array $bindings = [];

    public function load(): static
    {
        $manager = $this;
        if (file_exists(root_dir() . '/routes/channels.php')) {
            require_once(root_dir() . '/routes/channels.php');
        }
        return $this;
    }

    public function pattern(string $name = null, string $regex = null): static|array|string
    {
        if ($name !== null && $regex === null) {
            return \array_key_exists($name, $this->pattern) ? $this->pattern[$name] : $this->pattern['default'];
        }
        if ($name === null) {
            return $this->pattern;
        }
        $this->pattern[$name] = $regex;
        return $this;
    }

    public function bind(string $name, Closure $closure = null): static|callable|null
    {
        if ($closure === null) {
            if (\array_key_exists($name, $this->bindings)) {
                return $this->bindings[$name];
            }
            return null;
        }
        $this->bindings[$name] = $closure;
        return $this;
    }

    public function channel(string $channel, \Closure $callback): static
    {
        $this->channels[$channel] = $callback;
        return $this;
    }

    public function resolve(string $channel): Closure
    {
        foreach ($this->channels as $name => $authCallback) {
            $regex = $this->translateChannel($name);
            if (preg_match($regex, $channel, $match)) {
                $match = array_filter($match, function ($value, $key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_BOTH);
                foreach ($match as $key => &$value) {
                    if ($callback = $this->bind($key)) {
                        if ($callback instanceof Closure && $newValue = $callback($value)) {
                            $value = $newValue;
                            continue;
                        }
                        continue 2;
                    }
                }
                return function (string $channel, mixed $user) use ($authCallback, $match) {
                    $match['user'] = $user;
                    $match['channel'] = $channel;
                    $result = container()->call($authCallback, $match);
                    return (bool)$result;
                };
            }
        }
        return fn (string $channel, mixed $user) => false;
    }

    protected function translateChannel(string $channel): string
    {
        $patterns = $this->pattern();
        preg_match_all('/\{([a-zA-Z0-9]+)\}/m', $channel, $matches, PREG_SET_ORDER);
        $matches = array_map(function ($value) {
            return $value[1];
        }, $matches);
        foreach ($matches as $name) {
            $pattern = sprintf('(?<%s>%s)', $name, $patterns[$name] ?? $patterns['default']);
            $channel = str_replace('{' . $name . '}', $pattern, $channel);
        }
        $regex = '#^';
        $regex .= sprintf('(?<_url>%s)', $channel);
        $regex .= '$#i';
        return $regex;
    }

    public function auth(string $channel, mixed $user): bool
    {
        $callback = $this->resolve($channel);
        return $callback($channel, $user);
    }
}
