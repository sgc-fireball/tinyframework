<?php declare(strict_types=1);

namespace TinyFramework\Shell\TabCompletion;

class FunctionTabCompletion implements TabCompletionInterface
{

    public function getMatches(array $info, string $input, int $index): array
    {
        // @TODO implement token check
        if (preg_match('(^[^a-zA-Z])', $input)) {
            return [];
        }

        return array_filter(
            $this->getAllFunctions(),
            function (string $line) use ($input, $info) {
                return strpos($line, $input) === 0;
            }
        );
    }

    private function getAllFunctions(): array
    {
        static $functions = [];
        if (!isset($functions) || !is_array($functions) || empty($functions)) {
            $functions = [];
            foreach (get_defined_functions() as $land => $list) {
                $functions = array_merge($functions, array_values($list));
            }
        }
        return $functions;
    }

}
