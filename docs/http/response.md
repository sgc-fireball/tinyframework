# Response

- [Introduction](#introduction)

## Introduction
```php
use TinyFramework\Http\Response;

Response::new($content, $code, $headers);
Response::json($json, $code, $headers);
Response::error($code, $headers);
Response::redirect($to, $code, $headers);
Response::back($fallback, $headers);
Response::view($view, $data, $code, $headers);

$response = new Response();
$response
    ->protocol('HTTP/2')
    ->code(200)
    ->type('text/plain')
    ->header('X-Custom', time())
    ->headers(['X-Custom1' => time(), 'X-Custom2' => time()])
    ->content('<b>Hello, World</b>')
    ->send() // ->__toString()
;
```
## Examples
### HTML
```php
use TinyFramework\Http\Response;
return Response::new('Hello World');
```

### JSON
```php
use TinyFramework\Http\Response;
return Response::json(['success' => true]);
```

### Error
```php
use TinyFramework\Http\Response;
return Response::error(404);
```
