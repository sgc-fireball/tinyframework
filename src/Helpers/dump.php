<?php declare(strict_types=1);

if (!function_exists('dump')) {
    /**
     * @param mixed ...$val
     * @return void
     */
    function dump(...$val)
    {
        foreach ($val as $value) {
            echo php_sapi_name() !== 'cli' ? '<code><pre>' : '';
            var_dump($value);
            echo php_sapi_name() !== 'cli' ? '</pre></code>' : '';
        }
    }
}

if (!function_exists('dd')) {
    /**
     * @param mixed ...$val
     * @return void
     */
    function dd(...$val)
    {
        call_user_func_array('dump', $val);
        exit(1);
    }
}
