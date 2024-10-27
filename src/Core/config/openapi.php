<?php

return [

    'file' => env('OPENAPI_FILE_PATH', storage_dir('openapi.yaml')),

    'validate_request' => env('OPENAPI_VALIDATE_REQUEST', true),

    'validate_response' => env('OPENAPI_VALIDATE_RESPONSE', true),

];
