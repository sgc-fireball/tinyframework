<?php

declare(strict_types=1);

use TinyFramework\FileSystem\FtpFileSystem;
use TinyFramework\FileSystem\LocalFileSystem;
use TinyFramework\FileSystem\S3FileSystem;

return [
    'default' => env('FILESYSTEM_DRIVER', 'storage'),
    'storage' => [
        'driver' => LocalFileSystem::class,
        'basePath' => storage_dir(),
        'folderPermission' => env('FOLDER_PERMISSIONS', 0750),
        'filePermission' => env('FILE_PERMISSIONS', 0640),
    ],
    'public' => [
        'driver' => LocalFileSystem::class,
        'basePath' => public_dir(),
        'folderPermission' => env('FOLDER_PERMISSIONS', 0750),
        'filePermission' => env('FILE_PERMISSIONS', 0640),
    ],
    'ftp' => [
        'driver' => FtpFileSystem::class,
        'username' => env('FTP_USERNAME'),
        'password' => env('FTP_PASSWORD'),
        'host' => env('FTP_HOST'),
        'port' => env('FTP_PORT', 21),
        'ssl' => env('FTP_SSL', false),
        'passiv' => env('FTP_PASSIV', true),
        'timeout' => env('FTP_TIMEOUT', 10),
        'folderPermission' => env('FTP_FOLDER_PERMISSIONS', 0750),
        'filePermission' => env('FTP_FILE_PERMISSIONS', 0640),
    ],
    's3_pubic' => [
        'driver' => S3FileSystem::class,
        'access_key_id' => env('AWS_ACCESS_KEY_ID'),
        'secret_access_key' => env('AWS_SECRET_ACCESS_KEY'),
        'domain' => env('AWS_DOMAIN'),
        'public_domain' => env('AWS_PUBLIC_DOMAIN'),
        'region' => env('AWS_REGION', 'eu-central-1'),
        'bucket' => env('AWS_BUCKET_PUBLIC'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        'acl' => 'public-read',
    ],
    's3_private' => [
        'driver' => S3FileSystem::class,
        'access_key_id' => env('AWS_ACCESS_KEY_ID'),
        'secret_access_key' => env('AWS_SECRET_ACCESS_KEY'),
        'domain' => env('AWS_DOMAIN'),
        'public_domain' => null,
        'region' => env('AWS_REGION', 'eu-central-1'),
        'bucket' => env('AWS_BUCKET_PRIVATE'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        'acl' => 'private',
    ],
];
