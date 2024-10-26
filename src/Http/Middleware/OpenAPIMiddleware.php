<?php

namespace TinyFramework\Http\Middleware;

use Closure;
use TinyFramework\Http\RequestInterface;
use TinyFramework\Http\Response;
use TinyFramework\OpenAPI\OpenAPIValidator;
use TinyFramework\OpenAPI\Objects\OpenAPI;
use TinyFramework\OpenAPI\OpenAPIException;

class OpenAPIMiddleware implements MiddlewareInterface
{

    public function handle(RequestInterface $request, Closure $next, mixed ...$parameters): Response
    {
        $file = $parameters[0];
        $version = filemtime($file) ?? 0;
        $cacheKey = sprintf('openapi:%s:%d', $file, $version);

        $openAPI = cache()->tag(['openapi'])->remember($cacheKey, function () use ($file): ?OpenAPI {
            cache()->tag('openapi')->clear(); // remove all old versions

            if (str_ends_with($file, '.json')) {
                return OpenAPI::parseJsonFile($file);
            } elseif (str_ends_with($file, '.yaml')) {
                return OpenAPI::parseYamlFile($file);
            }
            return null;
        }, now()->addDay());

        if (!$openAPI) {
            return $next($request);
        }

        /**
         * openapi was loaded successful
         * validate the request
         */
        $openAPIValidator = new OpenAPIValidator($openAPI);
        try {
            $openAPIValidator->validateHttpRequest($request);
        } catch (OpenAPIException $e) {
            $code = max(max(400, $e->getCode()), 499);
            return Response::json([
                'error' => max(1, $e->getCode()),
                'message' => $e->getMessage(),
            ], $code);
        }

        /**
         * proceed the controller request
         */
        $response = $next($request);

        /**
         * validate the response
         */
        try {
            $openAPIValidator->validateHttpResponse($request, $response);
        } catch (OpenAPIException $e) {
            $code = min(max(500, $e->getCode()), 599);
            return Response::json([
                'error' => max(1, $e->getCode()),
                'message' => $e->getMessage(),
            ], $code);
        }
        return $response;
    }

}
