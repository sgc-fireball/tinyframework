<?php

namespace TinyFramework\OpenAPI;

enum SecuritySchemeType: string
{

    case API_KEY = 'apiKey';
    case HTTP = 'http';
    case MUTUAL_LS = 'mutualTLS';
    case OAUTH2 = 'oauth2';
    case OPEN_ID_CONNECT = 'openIdConnect';

}
