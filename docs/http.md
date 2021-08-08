# Http

- [Introduction](#introduction)
- [CORS](#cors)
- [Request](./http/request.md)
- [Response](./http/response.md)
- [Router](./http/router.md)
    - [Route](./http/router/route.md)
- [Url](./http/url.md)

## Introduction

### CORS

You can configurate the cors service in `config/cors.php` with the following parameters:

- allow_origins:
  A list of all allowed origins in format `schema://domain.tld`.
- max_age:
  Time specification in seconds, how long the browser may hold the cache result.
- allow_credentials:
  It is allowed to forward credentials?
  See [Access-Control-Allow-Credentials on developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Credentials)