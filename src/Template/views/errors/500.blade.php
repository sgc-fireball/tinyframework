<html>
<head>
    <title>500 Internal Server Error</title>
</head>
<body>
<h1>500 Internal Server Error</h1>
@if (config('app.debug'))
    <b>{{ get_class($e) }} [{{ $e->getCode() }}]</b>
    {{ str_replace([getcwd()], '', $e->getMessage()) }}
    in {{ ltrim(str_replace([getcwd()], '', $e->getFile()), '/') }}:{{ $e->getLine() }}
@endif
<p>Response-ID: {{ $response->id() }}</p>
</body>
</html>