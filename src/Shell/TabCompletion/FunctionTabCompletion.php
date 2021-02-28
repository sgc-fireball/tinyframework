<?php declare(strict_types=1);

namespace TinyFramework\Shell\TabCompletion;

use TinyFramework\Shell\Context;

class FunctionTabCompletion implements TabCompletionInterface
{

    private ?Context $context = null;

    public function setContext(?Context $context = null): TabCompletionInterface
    {
        $this->context = $context;
        return $this;
    }

    public function getMatches(array $info, string $input, int $index): array
    {
        if (preg_match('(^[^a-zA-Z_])', $input)) {
            return [];
        }

        return array_filter(
            $this->getAllFunctions(),
            function (string $line) use ($input) {
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
