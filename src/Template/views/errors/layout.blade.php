<!doctype html>
@section('html')
    <html lang="en">
        @section('html-inner')
            @section('head')
                <head>
                    @section('head-inner')
                        <title> @section('title') Error @show </title>
                        <meta name="robots" content="noindex">
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
                    @show
                </head>
            @show
            @section('body')
                <body>
                    @section('body-inner')
                        @section('header')@show
                        @section('content')
                            <main>
                                @section('content-inner')
                                    <h1> @section('headline') Headline @show </h1>
                                    @section('details')@show
                                @show
                            </main>
                        @show
                        @section('footer')
                            <footer>
                                <p>Response-ID: {{ $response->id() }}</p>
                            </footer>
                        @show
                    @show
                </body>
            @show
        @show
    </html>
@show
