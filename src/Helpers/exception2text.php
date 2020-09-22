<?php declare(strict_types=1);

if (!function_exists('exception2text')) {
    function exception2text(\Throwable $e): string
    {
        $result = sprintf(
            '%s[%d] %s in %s:%d',
            get_class($e),
            $e->getCode(),
            str_replace([getcwd()], '', $e->getMessage()),
            ltrim(str_replace([getcwd()], '', $e->getFile()), '/'),
            $e->getLine()
        );
        if ($e = $e->getPrevious()) {
            $result .= sprintf("\n - %s", exception2text($e));
        }
        return $result;
    }
}
