<?php declare(strict_types=1);

namespace TinyFramework\Core;

use Closure;
use RuntimeException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;

require_once(__DIR__ . "/../Helpers/functions.php");

class Container implements ContainerInterface
{

    static private ?Container $container = null;

    private array $aliases = [];

    private array $instances = [];

    private array $tags = [];

    private function __construct()
    {
        $this
            ->alias('container', self::class)
            ->alias(ContainerInterface::class, self::class)
            ->singleton(self::class, $this);
    }

    /**
     * @param string|string[] $tags
     * @param string|string[] $instances
     * @return self
     */
    public function tag($tags, $instances): ContainerInterface
    {
        $tags = is_string($tags) ? [$tags] : $tags;
        $instances = is_string($instances) ? [$instances] : $instances;
        foreach ($tags as $tag) {
            if (!array_key_exists($tag, $this->tags)) {
                $this->tags[$tag] = [];
            }
            $this->tags[$tag] = array_merge($this->tags[$tag], $instances);
            $this->tags[$tag] = array_unique($this->tags[$tag]);
        }
        return $this;
    }

    public function tagged(string $tag): array
    {
        return array_map(function (string $instance) {
            return $this->get($instance);
        }, $this->tags[$tag] ?? []);
    }

    public static function instance(): ContainerInterface
    {
        if (is_null(self::$container)) {
            self::$container = new self();
        }
        return self::$container;
    }

    public function has(string $key): bool
    {
        $key = $this->resolveAlias($key);
        return array_key_exists($key, $this->instances);
    }

    public function get(string $key, array $parameters = [])
    {
        $oKey = $key;
        $key = $this->resolveAlias($key);
        if (empty($parameters) && array_key_exists($key, $this->instances)) {
            if (is_callable($this->instances[$key]) || is_string($this->instances[$key])) {
                $this->instances[$key] = $this->call($this->instances[$key]);
            }
            return $this->instances[$key];
        }
        if (class_exists($key)) {
            return $this->call($key, $parameters);
        }
        throw new RuntimeException('Could not resolve ' . $oKey);
    }

    /**
     * @param mixed|string $key
     * @return mixed|string
     */
    public function resolveAlias($key)
    {
        if (is_string($key)) {
            while (array_key_exists($key, $this->aliases)) {
                $key = $this->aliases[$key];
            }
        }
        return $key;
    }

    public function singleton(string $key, $object)
    {
        $this->instances[$key] = $object;
        return $this;
    }

    public function alias(string $alias, string $key): ContainerInterface
    {
        $this->aliases[$alias] = $key;
        return $this;
    }

    public function decorator(string $key, $object)
    {
        $self = $this;
        $innerKey = uniqid('inner-' . $key . '-');
        $this->instances[$innerKey] = $this->instances[$key];
        $this->instances[$key] = function () use ($self, $object, $key, $innerKey) {
            $inner = $self->get($innerKey);
            $result = $self->get($object, ['inner' => $inner]);
            if (method_exists($result, 'setInner')) {
                $result->setInner($inner);
            }
            return $result;
        };
        return $this;
    }

    public function call($callable, array $parameters = [])
    {
        $callable = $this->resolveAlias($callable);
        if (is_string($callable)) {
            if (function_exists($callable)) {
                return $this->callFunction($callable, $parameters);
            }
            if (class_exists($callable)) {
                return $this->callConstruct($callable, $parameters);
            }
            if (strpos($callable, '@') !== false) {
                list ($class, $method) = explode('@', $callable, 2);
                if (!class_exists($class)) {
                    throw new RuntimeException('Could not found class ' . $class);
                }
                return $this->callMethod($class, $method, $parameters);
            }
        }
        if ($callable instanceof Closure) {
            return $this->callFunction($callable, $parameters);
        }
        if (is_object($callable) && method_exists($callable, '__invoke')) {
            return $this->callMethod($callable, '__invoke', $parameters);
        }
        if (is_array($callable) && method_exists($callable[0], $callable[1])) {
            return $this->callMethod($callable[0], $callable[1], $parameters);
        }
        throw new \RuntimeException('Could not call ' . var_export($callable, true));
    }

    /**
     * @param string $class
     * @param array $parameters
     * @return object
     * @throws \ReflectionException
     */
    private function callConstruct(string $class, array $parameters = [])
    {
        $arguments = [];
        $reflection = new ReflectionClass($class);

        /** if class supported singleton */
        if ($reflection->hasMethod('instance')) {
            $reflectionMethod = $reflection->getMethod('instance');
            if ($reflectionMethod->isPublic() && $reflectionMethod->isStatic()) {
                $arguments = $this->buildArgumentsByParameters($reflectionMethod, $parameters);
                return call_user_func([$class, 'instance'], $arguments);
            }
        }

        /** if class has a __construct method */
        if ($reflection->hasMethod('__construct')) {
            $reflectionMethod = $reflection->getMethod('__construct');
            if (!$reflectionMethod->isPublic()) {
                if ($reflection->hasMethod('instance') && $reflection->getMethod('instance')->isStatic()) {
                    return call_user_func([$class, 'instance']);
                }
            }
            $arguments = $this->buildArgumentsByParameters($reflectionMethod, $parameters);
        }
        if ($reflection->hasMethod('setContainer') && $reflection->getMethod('setContainer')->isStatic()) {
            call_user_func([$class, 'setContainer'], $this);
        }
        $instance = $reflection->newInstanceArgs($arguments);
        if ($reflection->hasMethod('setContainer') && !$reflection->getMethod('setContainer')->isStatic()) {
            $instance->setContainer($this);
        }
        return $instance;
    }

    /**
     * @param Object|string $class
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    private function callMethod($class, string $method, array $parameters = [])
    {
        if (!method_exists($class, $omethod = $method)) {
            $method = '__call';
        }
        if (!method_exists($class, $method)) {
            throw new RuntimeException('Could not found method ' . $class . '::' . $omethod);
        }
        $arguments = $this->buildArgumentsByParameters(
            new ReflectionMethod($class, $method),
            $parameters
        );
        return call_user_func_array([$this->call($class), $method], $arguments);
    }

    /**
     * @param Closure|string $function
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    private function callFunction($function, array $parameters = [])
    {
        $arguments = $this->buildArgumentsByParameters(
            new ReflectionFunction($function),
            $parameters
        );
        return call_user_func_array($function, $arguments);
    }

    /**
     * @param ReflectionFunction|ReflectionMethod $reflection
     * @param array $parameters
     * @return array
     */
    private function buildArgumentsByParameters($reflection, array $parameters = []): array
    {
        if (!($reflection instanceof ReflectionFunction || $reflection instanceof ReflectionMethod)) {
            throw new \InvalidArgumentException(sprintf(
                'Argument #1 must be a type of ReflectionFunction|ReflectionMethod, but %s given.',
                get_class($reflection)
            ));
        }
        $arguments = [];
        /**
         * @var int $index
         * @var \ReflectionParameter $reflectionParameter
         */
        foreach ($reflection->getParameters() as $index => $reflectionParameter) {
            /** @var null|\ReflectionNamedType $type */
            $type = $reflectionParameter->getType();
            if ($reflectionParameter->isVariadic()) {
                if ($index < count($parameters)) {
                    $values = array_slice(array_values($parameters), $index) ?? [];
                    $arguments = array_merge($arguments, $values);
                    break;
                }
            } else if (array_key_exists($reflectionParameter->name, $parameters)) {
                $arguments[$index] = $parameters[$reflectionParameter->name];
            } elseif ($type && $this->has($type->getName())) {
                $arguments[$index] = $this->get($type->getName());
            } elseif ($this->has($reflectionParameter->name)) {
                $arguments[$index] = $this->get($reflectionParameter->name);
            } elseif (array_key_exists($index, $parameters)) {
                $arguments[$index] = $parameters[$index];
            } else {
                $arguments[$index] = null;
            }
        }
        return $arguments;
    }

}
