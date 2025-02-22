<?php

namespace TinyFramework\OpenAPI\Objects;

use InvalidArgumentException;
use RuntimeException;
use stdClass;

/**
 * @link https://swagger.io/specification/#openapi-object
 */
class OpenAPI extends AbstractObject
{

    public string $openapi = '3.1.0';
    public Info|null $info = null;
    public ?string $jsonSchemaDialect = null;

    /** @var Server[] */
    public array $servers = [];

    /** @var null|stdClass<PathItem|Reference> */
    public ?object $paths = null;

    /** @var null|stdClass<PathItem|Reference> */
    public ?object $webhooks = null;

    /** @var ?Components */
    public ?Components $components = null;

    /** @var SecurityRequirement[] */
    public array $security = [];

    /** @var Tag[] */
    public array $tags = [];

    public ExternalDocumentation|null $externalDocs = null;

    public static function parseJsonFile(string $path): OpenAPI
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new InvalidArgumentException('Argument #1 of parseJsonFile must be a readable json file.');
        }
        return static::parseJson(file_get_contents($path));
    }

    public static function parseJson(string $json): OpenAPI
    {
        $arr = json_decode($json, true);
        if (!is_array($arr)) {
            throw new InvalidArgumentException('Argument #1 of parseJson must be a valid json string.');
        }
        return static::parse($arr);
    }

    public static function parseYamlFile(string $path): OpenAPI
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new InvalidArgumentException('Argument #1 of parseYamlFile must be a readable yaml file.');
        }
        return static::parseYaml(file_get_contents($path));
    }

    public static function parseYaml(string $yaml): OpenAPI
    {
        if (!extension_loaded('yaml')) {
            throw new RuntimeException('Please install the image ext-yaml first.');
        }
        $arr = yaml_parse($yaml);
        if (!is_array($arr)) {
            throw new InvalidArgumentException('Argument #1 of parseYaml must be a valid yaml string.');
        }
        return static::parse($arr);
    }

    public static function parse(array $arr): OpenAPI
    {
        $object = new OpenAPI();

        $object->openapi = $arr['openapi'] ?? '3.1.0';
        $object->info = Info::parse($arr['info']);

        if (array_key_exists('jsonSchemaDialect', $arr)) {
            $object->jsonSchemaDialect = $arr['jsonSchemaDialect'];
        }
        if (array_key_exists('servers', $arr)) {
            $object->servers = array_map(fn(array $server) => Server::parse($server), $arr['servers']);
        }
        if (array_key_exists('paths', $arr)) {
            $object->paths = new stdClass();
            foreach ($arr['paths'] as $key => $path) {
                if (array_key_exists('$ref', $path)) {
                    $object->paths->{$key} = Reference::parse($path);
                } else {
                    $object->paths->{$key} = PathItem::parse($path);
                }
            }
        }
        if (array_key_exists('webhooks', $arr)) {
            $object->webhooks = new stdClass();
            foreach ($arr['webhooks'] as $key => $webhook) {
                if (array_key_exists('$ref', $webhook)) {
                    $object->webhooks->{$key} = Reference::parse($webhook);
                } else {
                    $object->webhooks->{$key} = PathItem::parse($webhook);
                }
            }
        }
        if (array_key_exists('components', $arr)) {
            $object->components = new Components();
            $object->components = Components::parse($arr['components']);
        }
        if (array_key_exists('security', $arr)) {
            $object->security = array_map(function (array $security): SecurityRequirement {
                return SecurityRequirement::parse($security);
            }, $arr['security']);
        }
        if (array_key_exists('tags', $arr)) {
            $object->tags = array_map(fn(array $tag) => Tag::parse($tag), $arr['tags']);
        }
        if (array_key_exists('externalDocs', $arr)) {
            $object->externalDocs = ExternalDocumentation::parse($arr['externalDocs']);
        }
        return $object->parseExtension($arr)
            ->resolveReferences()
            ->resolveSecuritySchema();
    }

    protected function resolveReferences(): self
    {
        $resolveReferences = function (OpenAPI $openAPI, string $paths): ?AbstractObject {
            $steps = substr($paths, 2);
            $steps = explode('/', $steps);
            $result = $openAPI;
            foreach ($steps as $step) {
                if (property_exists($result, $step) && $result->{$step}) {
                    $result = $result->{$step};
                } else {
                    throw new RuntimeException('Could not resolve reference: ' . $paths);
                }
            }
            return $result;
        };

        $recursive = function (mixed &$object) use (&$recursive, $resolveReferences) {
            if ($object instanceof Reference) {
                $object->setReference($resolveReferences($this, $object->ref));
            } elseif (is_array($object)) {
                foreach ($object as &$value) {
                    $recursive($value);
                }
            } elseif (is_object($object)) {
                $values = get_object_vars($object);
                foreach ($values as &$value) {
                    if (is_object($value) || is_array($value)) {
                        $recursive($value);
                    }
                }
            }
        };
        $recursive($this);
        return $this;
    }

    protected function resolveSecuritySchema(): self
    {
        $resolveSecuritySchema = function (OpenAPI $openAPI, string $name): ?SecurityScheme {
            $result = $openAPI->components?->securitySchemes?->{$name} ?? null;
            if ($result instanceof Reference) {
                return $result->ref;
            }
            return $result;
        };

        $recursive = function (mixed &$object) use (&$recursive, $resolveSecuritySchema) {
            if ($object instanceof Reference) {
                // do nothing
            } elseif ($object instanceof SecurityRequirement) {
                if ($object->handler) {
                    foreach ($object->handler as $handler => $scopes) {
                        $object->setSecuritySchemaForHandler($handler, $resolveSecuritySchema($this, $handler));
                    }
                }
            } elseif (is_array($object)) {
                foreach ($object as &$value) {
                    $recursive($value);
                }
            } elseif (is_object($object)) {
                $values = get_object_vars($object);
                foreach ($values as &$value) {
                    if (is_object($value) || is_array($value)) {
                        $recursive($value);
                    }
                }
            }
        };
        $recursive($this);
        return $this;
    }

}
