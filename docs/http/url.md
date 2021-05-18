# Url

- [Introduction](#introduction)

## Introduction
The URL object basically represents the function of parse_url
and also extends this to an unparse function. Thus, when output
as a string, it is automatically assembled into a URL with
superfluous/standard port specifications automatically removed.
For example for HTTP port 80 is omitted, for HTTPS port 443 is removed.

```php
use TinyFramework\Http\URL;
$url = new TinyFramework\Http\URL('https://UsEr:p4ssw0rd@ex4mpl3.de:123/p4th/to/file?with=parameter#andFr4gment');

print_r($url);
TinyFramework\Http\URL Object
(
    [schema:TinyFramework\Http\URL:private] => https
    [user:TinyFramework\Http\URL:private] => UsEr
    [pass:TinyFramework\Http\URL:private] => p4ssw0rd
    [host:TinyFramework\Http\URL:private] => ex4mpl3.de
    [port:TinyFramework\Http\URL:private] => 123
    [path:TinyFramework\Http\URL:private] => /p4th/to/file
    [query:TinyFramework\Http\URL:private] => with=parameter
    [fragment:TinyFramework\Http\URL:private] => andFr4gment
)

echo $url; // https://UsEr:p4ssw0rd@ex4mpl3.de:123/p4th/to/file?with=parameter#andFr4gment
```

Each value can be retrieved and set with the getter and setter of the same name.
A setter automatically creates a clone of the original object.