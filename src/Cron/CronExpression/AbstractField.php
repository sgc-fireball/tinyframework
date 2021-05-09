<?php declare(strict_types=1);

namespace TinyFramework\Cron\CronExpression;

abstract class AbstractField
{

    protected int $minValue = 0;

    protected int $maxValue = 0;

    protected string $expression = '*';

    protected array|null $values = null;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    public function parse(): array
    {
        if ($this->values) {
            return $this->values;
        }

        $this->values = [];
        $expressions = explode(',', $this->expression);
        foreach ($expressions as $expression) {
            if (strlen($expression) === 0) {
                continue;
            }

            if ($expression === '*') {
                $this->values = range($this->minValue, $this->maxValue);
                continue;
            } else if (preg_match('/^(\d+)-(\d+)$/', $expression, $matches)) {
                if ($this->minValue <= intval($matches[1]) && intval($matches[1]) <= $this->maxValue) {
                    if ($this->minValue <= intval($matches[2]) && intval($matches[2]) <= $this->maxValue) {
                        if ($matches[1] <= $matches[2]) {
                            $this->values = array_merge($this->values, range(intval($matches[1]), intval($matches[2])));
                            continue;
                        }
                    }
                }
            } else if (preg_match('/^\*\/(\d+)$/', $expression, $matches)) {
                if ($this->minValue <= intval($matches[1]) && intval($matches[1]) <= $this->maxValue) {
                    if ($this->minValue === 0) {
                        $this->values = array_merge($this->values, [0]);
                    }
                    $this->values = array_merge($this->values, array_filter(
                            range($this->minValue, $this->maxValue),
                            fn(int $i) => $i % ($matches[1]) === 0)
                    );
                    continue;
                }
            } else if (preg_match('/^(\d+)-(\d+)\/(\d+)$/', $expression, $matches)) {
                if ($this->minValue <= intval($matches[1]) && intval($matches[1]) <= $this->maxValue) {
                    if ($this->minValue <= intval($matches[2]) && intval($matches[2]) <= $this->maxValue) {
                        if ($matches[1] <= $matches[2]) {
                            if ($this->minValue <= intval($matches[3]) && intval($matches[3]) <= $this->maxValue) {
                                if (intval($matches[1]) === 0) {
                                    $this->values = array_merge($this->values, [0]);
                                }
                                $this->values = array_merge($this->values, array_filter(
                                        range(intval($matches[1]), intval($matches[2])),
                                        fn(int $i) => $i % ($matches[3]) === 0)
                                );
                                continue;

                            }
                        }
                    }
                }
            } else {
                $expression = intval($expression);
                if ($this->minValue <= $expression && $expression <= $this->maxValue) {
                    $this->values = array_merge($this->values, [$expression]);
                    continue;
                }
            }
            throw new \RuntimeException('Invalid cron expression: ' . get_class($this) . '(' . $this->expression . ')');
        }

        if (empty($this->values)) {
            throw new \RuntimeException('Invalid cron expression: ' . get_class($this) . '(' . $this->expression . ')');
        }

        sort($this->values);
        $this->values = $this->fixList(array_values(array_unique($this->values)));
        return $this->values;
    }

    public function inExpression(int $value): bool
    {
        $list = $this->parse();
        #printf("Search %d in %s(%s)\n", $value, get_class($this), implode(',', $list));
        return in_array($value, $list);
    }

    protected function fixList(array $list): array
    {
        return $list;
    }

    abstract public function increment(\DateTime $time): \DateTime;

    abstract public function decrement(\DateTime $time): \DateTime;

}
