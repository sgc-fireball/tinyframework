# Blade

- [Introduction](#introduction)
- [Funcitons](#funcitons)
- [Features](#features)
- [Todos](#todos)

## Introduction

The TinyFramework does not support the official blade functions. However, it emulates most of them.

## Funcitons

### exists

```php
use TinyFramework\Template\Blade;
/** @var Blade $view */
$view->exists('path.to.template'); // true if resources/views/path/to/template.blade.php exists
```

### render

```php
use TinyFramework\Template\Blade;
/** @var Blade $view */
$view->render('path.to.template', ['key' => 'value']); // string
// render resources/views/path/to/template.blade.php with view data
// $key = "value"; 
```

### renderString

```php
use TinyFramework\Template\Blade;
/** @var Blade $view */
$view->render('<b>{{ $key }}</b>', ['key' => 'value']); // string
// <b>value</b>
```

### compileFile

These function precompiles the template file into php code.

```php
use TinyFramework\Template\Blade;
/** @var Blade $view */
$view->compileFile('path.to.template'); // php code
```

### compileString

These function precompiles the string into php code.

```php
use TinyFramework\Template\Blade;
/** @var Blade $view */
$view->compileString('<b>{{ $key }}</b>'); // php code
```

## Features

### Verbatim

```blade
@verbatim
    Theses lines will not @interpreted by blade.
@endverbatim
```

```blade
@@this_is_not_a_blade_comment

// output
@this_is_not_a_blade_comment
```

### Class

```blade
<span class="@class(['alert' => 1, alert-error' => 0, 'alert-info' => 1])">
  Info
</span>
```

### Comments

```blade
{{-- comment --}}
```

### Echo

```blade
{{ "output encoded code" }}
{!! "output <br> html code" !}}
```

### PHP Code

```blade
@php
    $test = time(); echo $test;
@endphp
```

### Props
```blade
@props(['time' => time()])
Time: {{ $time }}
```

### if-then-else

```blade
@if (time() % 2)
    even
@else
    odd
@endif
```

### if empty

```blade
@empty($var)
   $var is empty
@endif
```

### if isset

```blade
@isset($var)
   $var is set!
@endif
```

### switch case default

```blade
@switch($var)
@case(1)
    Var is one.
@break
@case(2)
    Var is two.
@break
@default
    Var is anyother value then one or two.
@endswitch
```

### inject

```blade
@inject('templateVar', 'service-id-or-class')
{{ $templateVar->count }}
```

### break (if)

```blade
@break
@break($var === 1)
```

### continue (if)

```blade
@continue
@continue($var === 1)
```

### dump

```blade
@dump($var)
```

### dd

```blade
@dd($var)
```

### while

```blade
@while($var)
    output
@endwhile
```

### foreach

```blade
@foreach($var as $key => $value)
    {{ $key }}: {{ $value }}
@endwhile
```

### for

```blade
@for($i=0;$i<10;$i++)
    {{ $i }}
@endwhile
```

### json

Output the json_encode value of $var.

```blade
@json($var)
```

### unset

Unset the $var from template.

```blade
@unset($var) // delete the variable
```

### trans

Output the translation from resources/lang/<lang>/file.php of

```php
<?php 
return ['key1.key2' => 'value'];
```

```blade
@trans('file.key1.key2')
```

### extends

layout.blade.php

```blade
<html>
    <head> @yield('head') </head>
    <head> @yield('body') </head>
</html>
```

page/home.blade.php

```blade
@extends('layout')
@section('head')
    <title>Home</title>
@endsection
@section('body')
    <h1>Home</h1>
@endsection
```

### include

```blade
@include('layout.nav')
@include('layout.inc.link', ['title' => 'Link', 'href' => '/'])
```

### section

You can open a section with `@section`. But you can close a section with many differnt types:

- show
    ```blade
    @section('sec2')
        This is a block that can be overwrite.
    @show
    ```

- endsection
    ```blade
    @section('sec1')
        Overwrite the main content
    @endsection
    ```

- append
  ```blade
    @section('sec2')
        Append the main block.
    @append
    ```

- prepend
    ```blade
    @section('sec2')
        Prepend the main block.
    @append
    ```

- stop
- overwrite

### yield

A `@yield` is a placeholder, that can be filled with sections.

### parent

A `@parent` tag, is a placeholder in a section. On these position the parent content will be filled in.

### content

TBD

## Todos

- env
- hasSection
- sectionMissing
- forelse
- includeIf
- includeWhen
- includeUnless
- includeFirst
- each
- component
- csrf
- method
