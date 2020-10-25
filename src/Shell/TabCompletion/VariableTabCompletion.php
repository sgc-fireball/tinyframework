<?php declare(strict_types=1);

namespace TinyFramework\Shell\TabCompletion;

class VariableTabCompletion implements TabCompletionInterface
{

    public function getMatches(array $info, string $input, int $index): array
    {
        $variables = [];
        if (empty($input)) {
            return [];
        }
        $variables = array_filter(array_keys($variables), function (string $var) use ($input) {
            return strpos($var, $input) === 0;
        });
        $variables = array_merge($variables, array_map(function(string $var) {
            return '$'. $var;
        }, $variables));
        return $variables;
    }

}
