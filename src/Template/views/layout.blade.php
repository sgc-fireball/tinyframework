<!DOCTYPE html>
<html dir="ltr">
<head>
    <title>TinyFramework</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    @section('style')
        <style type="text/css">
            @verbatim
            html {
                background-color: #eeeeee;
                color: #222222;
                line-height: 1.5rem;
            }
            @media screen and (prefers-color-scheme: dark) {
                html {
                    background-color: #222222;
                    color: #eeeeee;
                }
            }
            @endverbatim
        </style>
    @show
</head>
<body>
@section('header')
    <header>
        @section('header-inner')
        @show
    </header>
@show
@section('nav')
    <nav>
        @section('nav-inner')
        @show
    </nav>
@show
@section('content')
    <main>
        @section('content-inner')
        @show
    </main>
@show
@section('footer')
    <footer>
        @section('footer-inner')
        @show
    </footer>
@show
</body>
</html>
