<?php

declare(strict_types=1);

use TinyFramework\FileSystem\FtpFileSystem;
use TinyFramework\FileSystem\LocalFileSystem;
use TinyFramework\FileSystem\S3FileSystem;
use TinyFramework\FileSystem\SftpFileSystem;

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
        'filePermission' => env('FTP_FILE_PERMISSIONS', 0640),
        'folderPermission' => env('FTP_FOLDER_PERMISSIONS', 0750),
    ],
    'sftp' => [
        'driver' => SftpFileSystem::class,
        'username' => env('SFTP_USERNAME'),
        'pubkeyfile' => env('SFTP_PUBIC_KEY'),
        'privkeyfile' => env('SFTP_PRIVATE_KEY'),
        'password' => env('SFTP_PRIVATE_KEY_PASSWORD'),
        'host' => env('SFTP_HOST'),
        'port' => env('SFTP_PORT', 21),
        'filePermission' => env('SFTP_FILE_PERMISSIONS', 0640),
        'folderPermission' => env('SFTP_FOLDER_PERMISSIONS', 0750),
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
        'root' => env('AWS_PUBLIC_ROOT', null),
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
        'root' => env('AWS_PRIVATE_ROOT', null),
        'acl' => 'private',
    ],
];
