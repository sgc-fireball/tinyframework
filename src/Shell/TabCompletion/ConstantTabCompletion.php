<?php declare(strict_types=1);

namespace TinyFramework\Shell\TabCompletion;

use TinyFramework\Shell\Context;

class ConstantTabCompletion implements TabCompletionInterface
{

    private ?Context $context = null;

    public function setContext(?Context $context = null): TabCompletionInterface
    {
        $this->context = $context;
        return $this;
    }

    public function getMatches(array $info, string $input, int $index): array
    {
        if (empty($info['line_buffer'])) {
            return [];
        }
        if (empty($input)) {
            return [];
        }
        return array_filter(
            array_keys(get_defined_constants()),
            function (string $constant) use ($input) {
                return strpos($constant, $input) === 0;
            }
        );
    }

}
