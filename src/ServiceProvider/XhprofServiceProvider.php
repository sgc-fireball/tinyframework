<?php

declare(strict_types=1);

namespace TinyFramework\ServiceProvider;

use TinyFramework\Core\ConfigInterface;
use TinyFramework\Core\KernelInterface;
use TinyFramework\Http\Request;
use TinyFramework\Http\Response;

class XhprofServiceProvider extends ServiceProviderAwesome
{
    public function register(): void
    {
        if (!extension_loaded('tideways_xhprof') && !extension_loaded('xhprof')) {
            return;
        }

        $config = $this->container->get('config');
        assert($config instanceof ConfigInterface);
        if (!$config->get('xhprof.enable')) {
            return;
        }

        /** check percent rate */
        $percent = random_int(1, 100);
        if ($percent > $config->get('xhprof.percent')) {
            return;
        }

        $dir = $config->get('xhprof.dir');
        $expire = time() - $config->get('xhprof.expire');

        if (function_exists('\\tideways_xhprof_enable')) {
            \tideways_xhprof_enable(
                \TIDEWAYS_XHPROF_FLAGS_NO_BUILTINS | \TIDEWAYS_XHPROF_FLAGS_MEMORY | \TIDEWAYS_XHPROF_FLAGS_CPU | \TIDEWAYS_XHPROF_FLAGS_MEMORY_ALLOC
            );
        } elseif (function_exists('\\xhprof_enable')) {
            xhprof_enable(
                XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY,
                [
                    'ignored_functions' => [
                        'call_user_func',
                        'call_user_func_array',
                        'Composer\Autoload\ClassLoader::loadClass',
                        'Composer\Autoload\includeFile',
                        'TinyFramework\Core\Container::call',
                        'TinyFramework\Core\Container::callFunction',
                        'TinyFramework\Core\Container::callMethod',
                    ],
                ]
            );
        } else {
            return;
        }

        $kernel = $this->container->get('kernel');
        assert($kernel instanceof KernelInterface);

        if (!$kernel->runningInConsole() && method_exists($kernel, 'terminateRequestCallback')) {
            $kernel->terminateRequestCallback(function (Request $request, Response $response) use ($dir, $expire) {
                if (function_exists('\\tideways_xhprof_disable')) {
                    $data = \tideways_xhprof_disable();
                } elseif (function_exists('\\xhprof_disable')) {
                    $data = xhprof_disable();
                } else {
                    return;
                }

                if (str_starts_with($request->url()->path(), '/__xhprof')) {
                    return;
                }

                /**
                 * @see https://github.com/bbc/programmes-xhprof/blob/master/Document/XhguiRuns.php#L51
                 */
                $end = microtime(true);
                $data = [
                    '_id' => $request->id() . '-' . $response->id(),
                    'meta' => [
                        'url' => $request->url()->userInfo('', '')->fragment('')->__toString(),
                        'SERVER' => $request->server(),
                        'get' => $request->get(),
                        'env' => [],
                        'simple_url' => $request->url()->userInfo('', '')->fragment('')->__toString(),
                        'request_ts' => round(TINYFRAMEWORK_START),
                        'request_ts_micro' => [
                            'sec' => (int)TINYFRAMEWORK_START,
                            'usec' => (TINYFRAMEWORK_START - (int)TINYFRAMEWORK_START) * 10000,
                        ],
                        'request_date' => date('Y-m-d\TH:i:sP', (int)TINYFRAMEWORK_START),
                        'request_duration' => $end - TINYFRAMEWORK_START,

                        'request_id' => $request->id(),
                        'request_method' => $request->method(),

                        'response_id' => $response->id(),
                        'response_code' => $response->code(),
                        'response_ts' => round($end),
                        'response_ts_micro' => [
                            'sec' => (int)$end,
                            'usec' => ($end - (int)$end) * 10000,
                        ],
                        'response_date' => date('Y-m-d\TH:i:sP', (int)$end),
                    ],
                    'profile' => $data,
                ];
                $file = $request->id() . '-' . $response->id() . '.xhprof';
                file_put_contents($dir . '/' . $file, json_encode($data, JSON_PRETTY_PRINT));
                chmod($dir . '/' . $file, 0600);
                tmpreaper($dir, $expire);
            });
        }
    }
}
