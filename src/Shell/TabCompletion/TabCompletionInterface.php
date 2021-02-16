<?php declare(strict_types=1);

namespace TinyFramework\Shell\TabCompletion;

use TinyFramework\Shell\Context;

interface TabCompletionInterface
{

    public function getMatches(array $info, string $input, int $index): array;

    public function setContext(?Context $context = null): TabCompletionInterface;

}
