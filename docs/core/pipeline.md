# Pipeline

- [Introduction](#introduction)

## Introduction
The pipeline pattern is perfect to execute security concepts or modifiers before a core logic.

## Examples
### Example 1
Source Code:
```php
use TinyFramework\Core\Pipeline;

/** @var mixed $parameter */
$parameter = null;
$p = new Pipeline();
$p->layers(function($parameter, Closure $next) {
    echo "Layer1:start\n";
    $response = $next($parameter);
    echo "Layer1:end\n";
    return $response;
});
$p->layers(function($parameter, Closure $next) {
    echo "Layer2:start\n";
    $response = $next($parameter);
    echo "Layer2:end\n";
    return $response;
});
$p->layers(function($parameter, Closure $next) {
    echo "Layer3:start\n";
    $response = $next($parameter);
    echo "Layer3:end\n";
    return $response;
});
$p->call(function($parameter) {
    echo "Core\n";
}, $parameter);
```
Output:
```text
Layer1:start
Layer1:start
Layer1:start
Core
Layer3:end
Layer2:end
Layer1:end
```

### Example 2
Source Code:
```php
use TinyFramework\Core\Pipeline;

/** @var mixed $parameter */
$parameter = null;
$p = new Pipeline();
$p->layers(function($parameter, Closure $next) {
    echo "Layer1:start\n";
    $response = $next($parameter);
    echo "Layer1:end\n";
    return $response;
});
$p->layers(function($parameter, Closure $next) {
    echo "Layer2:start\n";
    echo "Layer2:end\n";
    return null;
});
$p->layers(function($parameter, Closure $next) {
    echo "Layer3:start\n";
    $response = $next($parameter);
    echo "Layer3:end\n";
    return $response;
});
$p->call(function($parameter) {
    echo "Core\n";
}, $parameter);
```
Output:
```text
Layer1:start
Layer1:start
Layer2:end
Layer1:end
```
