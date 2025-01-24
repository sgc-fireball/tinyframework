<?php

namespace TinyFramework\Cron;

abstract class CronjobAwesome implements CronjobInterface
{

    private array $start = [];
    private array $end = [];
    private array $successful = [];
    private array $failed = [];
    private array $skip = [];

    abstract public function expression(): string;

    abstract public function handle(): void;

    public function onStart(callable|string|\Closure|array|null $start = null): self
    {
        if ($start) {
            $this->start[] = $start;
        } else {
            foreach ($this->start as $callback) {
                rescue(fn() => container()->call($callback, [$this]));
            }
        }
        return $this;
    }

    public function onEnd(callable|string|\Closure|array|null $end = null): self
    {
        if ($end) {
            $this->end[] = $end;
        } else {
            foreach ($this->end as $callback) {
                rescue(fn() => container()->call($callback, [$this]));
            }
        }
        return $this;
    }

    public function onSuccess(callable|string|\Closure|array|null $success = null): self
    {
        if ($success) {
            $this->successful[] = $success;
        } else {
            foreach ($this->successful as $callback) {
                rescue(fn() => container()->call($callback, [$this]));
            }
        }
        return $this;
    }

    public function onFailed(callable|string|\Closure|array|null $failed = null): self
    {
        if ($failed) {
            $this->failed[] = $failed;
        } else {
            foreach ($this->failed as $callback) {
                rescue(fn() => container()->call($callback, [$this]));
            }
        }
        return $this;
    }

    public function skip(callable|string|\Closure|array|null $skip = null): self|bool
    {
        if ($skip) {
            $this->skip[] = $skip;
            return $this;
        } else {
            foreach ($this->skip as $callback) {
                if (rescue(fn() => container()->call($callback, [$this]), false)) {
                    return true;
                }
            }
            return false;
        }
    }

    public function withoutOverlapping(): self
    {
        $id = 'cronjob:' . md5(get_class($this)) . ':without-overlapping:' . gethostname();
        $this->onStart(function () use ($id): void {
            cache()->set($id, time());
        });
        $this->onEnd(function () use ($id): void {
            cache()->forget($id);
        });
        $this->onSuccess(function () use ($id): void {
            cache()->forget($id);
        });
        $this->onFailed(function () use ($id): void {
            cache()->forget($id);
        });
        $this->skip(function () use ($id): bool {
            return cache()->has($id);
        });
        return $this;
    }

    public function onOneServer(): self
    {
        $id = 'cronjob:' . md5(get_class($this)) . ':on-one-server';
        $this->onStart(function () use ($id): void {
            cache()->set($id, time());
        });
        $this->onEnd(function () use ($id): void {
            cache()->forget($id);
        });
        $this->onSuccess(function () use ($id): void {
            cache()->forget($id);
        });
        $this->onFailed(function () use ($id): void {
            cache()->forget($id);
        });
        $this->skip(function () use ($id): bool {
            return cache()->has($id);
        });
        return $this;
    }

}
