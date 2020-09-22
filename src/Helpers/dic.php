<?php declare(strict_types=1);

use TinyFramework\Http\Response;

if (!function_exists('container')) {
    /**
     * @param string|null $key
     * @return mixed|TinyFramework\Core\Container
     */
    function container(string $key = null)
    {
        return \TinyFramework\Core\Container::instance()->get($key);
    }
}

if (!function_exists('config')) {
    /**
     * @param string|null $key
     * @return mixed|\TinyFramework\Core\Config
     */
    function config($key = null, $value = null)
    {
        $config = container('config');
        if (is_null($key)) {
            return $config;
        }
        if (!is_null($value)) {
            return $config->set($key, $value);
        }
        return $config->get($key);
    }
}

if (!function_exists('cache')) {
    function cache(): \TinyFramework\Cache\CacheInterface
    {
        return container('cache');
    }
}

if (!function_exists('session')) {
    function session(): \TinyFramework\Session\SessionInterface
    {
        return container('session');
    }
}

if (!function_exists('event')) {
    function event(): \TinyFramework\Event\EventDispatcherInterface
    {
        return container('event');
    }
}

if (!function_exists('logger')) {
    function logger(): \TinyFramework\Logger\LoggerInterface
    {
        return container('logger');
    }
}

if (!function_exists('queue')) {
    function queue(): \TinyFramework\Queue\QueueInterface
    {
        return container('queue');
    }
}

if (!function_exists('view')) {
    /**
     * @param string|null $file
     * @param array $data
     * @return \TinyFramework\Http\Response|\TinyFramework\Template\ViewInterface
     */
    function view(string $file = null, array $data = [])
    {
        /** @var \TinyFramework\Template\ViewInterface $view */
        $view = container('view');
        if (is_null($file)) {
            return $view;
        }
        $content = $view->render($file, $data);
        return Response::new($content);
    }
}
