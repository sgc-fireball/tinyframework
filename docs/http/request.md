# Request

- [Introduction](#introduction)

## Introduction
All setter functions create a new instance with cloned values. The original object is not changed.

```php
$request = TinyFramework\Http\Request::fromGlobal();
$request->id(); // uuid
$request->get(); // return the _GET array 
$request->get('field'); // return the _GET['field'] array value
$request->get(['field' => '123']); // overwrite the _GET['field']
$request->get('field', '123'); // overwrite the _GET['field']
$request->post(); // return the _POST array 
$request->post('field'); // return the _POST['field'] array value
$request->post(['field' => '123']); // overwrite the _POST['field']
$request->post('field', '123'); // overwrite the _POST['field']
$request->cookie(); // return the _COOKIE array 
$request->cookie('field'); // return the _COOKIE['field'] array value
$request->cookie(['field' => '123']); // overwrite the _COOKIE['field']
$request->cookie('field', '123'); // overwrite the _COOKIE['field']
$request->file(); // return the _FILES array 
$request->file('field'); // return the _FILES['field'] array value
$request->file(['field' => '123']); // overwrite the _FILES['field']
$request->file('field', '123'); // overwrite the _FILES['field']
$request->route(); // return the current route object, if matched
$request->session(); // return the current session, if started
$request->user(); // return the current user information (mixed type)
$request->method(); // return the current method 
$request->method('POST'); // overwrite the method
$request->url(); // return the current uri 
$request->url(new \TinyFramework\Http\URL(), true); // overwrite the uri and preserve the hostname
$request->protocol(); // return the current protocol 
$request->protocol('HTTP/2'); // overwrite the protocol
$request->header(); // return all headers as array
$request->header('X-Token'); // return an array with all values of X-Token headers
$request->header('content-type', 'applicaton/json'); // overwrite the content-type header
$request->server(); // return all _SERVER['HTTP_*'] as array
$request->server('http_host'); // return an array with all values of _SERVER['HTTP_HOST']
$request->server('http_host', 'example.de'); // overwrite the http_host
$request->body(); // return the raw body string
$request->body('asdasd'); // overwrite the raw body string
$request->ip(); // return the client ip address
$request->ip('127.0.0.1'); // overwrite the client ip address
```
