<?php declare(strict_types=1);

namespace TinyFramework\Shell\TabCompletion;

class ClassTabCompletion implements TabCompletionInterface
{

    public function getMatches(array $info, string $input, int $index): array
    {
        $line = $info['line_buffer'] ?? '';
        $tokens = \token_get_all('<?php ' . $line);

        $search = '';
        while ($item = array_pop($tokens)) {
            list($tokenIndex, $content, $row) = $item;
            if (!in_array($tokenIndex, [T_NS_SEPARATOR, T_STRING])) {
                break;
            }
            $search = $content . $search;
        }

        $replace = $search = ltrim($search, '\\');
        if (strpos($search, '\\') !== false) {
            $replace = explode('\\', $replace);
            array_pop($replace);
            $replace = implode('\\', $replace) . '\\';
        } else {
            $replace = '';
        }

        $classes = get_declared_classes();
        if ($search) {
            $classes = array_filter($classes, function (string $class) use ($search) {
                return strpos($class, $search) === 0;
            });
        }

        if ($replace) {
            $classes = array_map(function (string $class) use ($replace) {
                return preg_replace('(^' . preg_quote($replace) . ')', '', $class);
            }, $classes);
        }
        return $classes;
    }

}
