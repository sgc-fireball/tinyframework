<?php declare(strict_types=1);

namespace TinyFramework\Shell\TabCompletion;

class ConstantTabCompletion implements TabCompletionInterface
{

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
