<?php

namespace TinyFramework\OpenAPI;

enum SecuritySchemeIn: string
{

    case QUERY = 'query';
    case HEADER = 'header';
    case COOKIE = 'cookie';

}
