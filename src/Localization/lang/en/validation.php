<?php

return [
    'accepted' => 'The {attribute} must be accepted.',
    'array' => 'The {attribute} must be an array.',
    'between' => [
        'numeric' => 'The {attribute} must be between {min} and {max}.',
        'file' => 'The {attribute} must be between {min} and {max} kilobytes.',
        'string' => 'The {attribute} must be between {min} and {max} characters.',
        'array' => 'The {attribute} must have between {min} and {max} items.',
    ],
    'boolean' => 'The {attribute} field must be true or false.',
    'confirmed' => 'The {attribute} confirmation does not match.',
    'email' => 'The {attribute} must be a valid email address.',
#    'exists' => 'The selected {attribute} is invalid.',
    'file' => 'The {attribute} must be a file.',
    'inlinefile' => 'The {attribute} must be an inline file.',
    'filled' => 'The {attribute} field must have a value.',
#    'gt' => [
#        'numeric' => 'The {attribute} must be greater than {value}.',
#        'file' => 'The {attribute} must be greater than {value} kilobytes.',
#        'string' => 'The {attribute} must be greater than {value} characters.',
#        'array' => 'The {attribute} must have more than {value} items.',
#    ],
#    'gte' => [
#        'numeric' => 'The {attribute} must be greater than or equal {value}.',
#        'file' => 'The {attribute} must be greater than or equal {value} kilobytes.',
#        'string' => 'The {attribute} must be greater than or equal {value} characters.',
#        'array' => 'The {attribute} must have {value} items or more.',
#    ],
    'image' => 'The {attribute} must be an image.',
    'in' => 'The selected {attribute} is invalid.',
#    'in_array' => 'The {attribute} field does not exist in {other}.',
    'integer' => 'The {attribute} must be an integer.',
    'ip' => 'The {attribute} must be a valid IP address.',
    'ipv4' => 'The {attribute} must be a valid IPv4 address.',
    'ipv6' => 'The {attribute} must be a valid IPv6 address.',
    'json' => 'The {attribute} must be a valid JSON string.',
#    'lt' => [
#        'numeric' => 'The {attribute} must be less than {value}.',
#        'file' => 'The {attribute} must be less than {value} kilobytes.',
#        'string' => 'The {attribute} must be less than {value} characters.',
#        'array' => 'The {attribute} must have less than {value} items.',
#    ],
#    'lte' => [
#        'numeric' => 'The {attribute} must be less than or equal {value}.',
#        'file' => 'The {attribute} must be less than or equal {value} kilobytes.',
#        'string' => 'The {attribute} must be less than or equal {value} characters.',
#        'array' => 'The {attribute} must not have more than {value} items.',
#    ],
    'max' => [
        'numeric' => 'The {attribute} must not be greater than {max}.',
        'file' => 'The {attribute} must not be greater than {max} kilobytes.',
        'string' => 'The {attribute} must not be greater than {max} characters.',
        'array' => 'The {attribute} must not have more than {max} items.',
    ],
    'mimetypes' => 'The {attribute} must be a file of type: {values}.',
    'min' => [
        'numeric' => 'The {attribute} must be at least {min}.',
        'file' => 'The {attribute} must be at least {min} kilobytes.',
        'string' => 'The {attribute} must be at least {min} characters.',
        'array' => 'The {attribute} must have at least {min} items.',
    ],
    'not_in' => 'The selected {attribute} is invalid.',
    'numeric' => 'The {attribute} must be a number.',
    'password' => [
        'to_short' => '{attribute} is to short.',
        'uppercase' => '{attribute} is missing upper case chars.',
        'lowercase' => '{attribute} is missing lower case chars.',
        'numerics' => '{attribute}s is missing numerics.',
        'symbols' => '{attribute}s is missing symbols.',
        'pwned' => '{attribute} is insecure. For more information, please see https://haveibeenpwned.com/Passwords',
    ],
    'present' => 'The {attribute} field must be present.',
#    'regex' => 'The {attribute} format is invalid.',
    'required' => 'The {attribute} field is required.',
#    'required_if' => 'The {attribute} field is required when {other} is {value}.',
#    'required_unless' => 'The {attribute} field is required unless {other} is in {values}.',
    'prohibited' => 'The {attribute} field is prohibited.',
    'string' => 'The {attribute} must be a string.',
    'timezone' => 'The {attribute} must be a valid zone.',
#    'unique' => 'The {attribute} has already been taken.',
    'url' => 'The {attribute} is not a valid URL.',
    'video' => 'The {attribute} must be a video.',
];
