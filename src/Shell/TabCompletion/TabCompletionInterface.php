<?php declare(strict_types=1);

namespace TinyFramework\Shell\TabCompletion;

interface TabCompletionInterface
{

    public function getMatches(array $info, string $input, int $index): array;

}
