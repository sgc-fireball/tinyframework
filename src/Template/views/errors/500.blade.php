<!doctype html>
<html lang="en">
<head>
    <title>500 Internal Server Error</title>
    <meta name="robots" content="noindex">
</head>
<body>
<h1>500 Internal Server Error</h1>
@if (config('app.debug'))
    <strong>{{ get_class($e) }} [{{ $e->getCode() }}]</strong>
    {{ str_replace([getcwd()], '', $e->getMessage()) }}
    in {{ ltrim(str_replace([getcwd()], '', $e->getFile()), '/') }}:{{ $e->getLine() }}
@endif
<p>Response-ID: {{ $response->id() }}</p>
</body>
</html>
