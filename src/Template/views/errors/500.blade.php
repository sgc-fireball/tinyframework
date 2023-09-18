<!doctype html>
<html lang="en">
<head>
    <title>500 Internal Server Error</title>
    <meta name="robots" content="noindex">
</head>
<body>
<h1>500 Internal Server Error</h1>
@if (config('app.debug'))
    <p><strong>{{ get_class($e) }} [{{ $e->getCode() }}]</strong>
    {{ str_replace([root_dir()], '', $e->getMessage()) }}
    <br>
    in {{ ltrim(str_replace([root_dir()], '', $e->getFile()), '/') }}:{{ $e->getLine() }}</p>
@endif
@if ($e instanceof TinyFramework\Validation\ValidationException)
    <ul>
        @foreach ($e->getErrorBag() as $field => $errors)
            <li><b>{{ $field }}</b></li>
            <li>
                <ul>
                    @foreach ($errors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ul>
@endif
<p>Response-ID: {{ $response->id() }}</p>
</body>
</html>
