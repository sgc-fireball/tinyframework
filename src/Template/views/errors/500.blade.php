@extends('errors.layout')

@section('title')
    500 Internal Server Error
@endsection

@section('headline')
    500 Internal Server Error
@endsection

@section('details')
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
@endsection
