<?php

namespace TinyFramework\OpenAPI;

enum ParameterIn: string
{

    case QUERY = 'query';
    case HEADER = 'header';
    case PATH = 'path';
    case COOKIE = 'cookie';

}
