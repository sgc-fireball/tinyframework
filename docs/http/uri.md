# Uri

- [Introduction](#introduction)

## Introduction
The URI object basically represents the function of parse_url
and also extends this to an unparse function. Thus, when output
as a string, it is automatically assembled into a URL with
superfluous/standard port specifications automatically removed.
For example for HTTP port 80 is omitted, for HTTPS port 443 is removed.

```php
use TinyFramework\Http\Uri;
$uri = new TinyFramework\Http\Uri('https://UsEr:p4ssw0rd@ex4mpl3.de:123/p4th/to/file?with=parameter#andFr4gment');

print_r($uri);
TinyFramework\Http\Uri Object
(
    [schema:TinyFramework\Http\Uri:private] => https
    [user:TinyFramework\Http\Uri:private] => UsEr
    [pass:TinyFramework\Http\Uri:private] => p4ssw0rd
    [host:TinyFramework\Http\Uri:private] => ex4mpl3.de
    [port:TinyFramework\Http\Uri:private] => 123
    [path:TinyFramework\Http\Uri:private] => /p4th/to/file
    [query:TinyFramework\Http\Uri:private] => with=parameter
    [fragment:TinyFramework\Http\Uri:private] => andFr4gment
)

echo $uri; // https://UsEr:p4ssw0rd@ex4mpl3.de:123/p4th/to/file?with=parameter#andFr4gment
```

Each value can be retrieved and set with the getter and setter of the same name.
A setter automatically creates a clone of the original object.