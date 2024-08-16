# Filesystem

- [Introduction](#introduction)
- [Filesytems](#filesytems)
    - [storage](#storage)
    - [public](#public)
    - [ftp](#ftp)
    - [S3 Pubic](#s3_pubic)
    - [S3 private](#s3_private)

## Introduction

```php
filesystem('public')->fileExists(string $location): bool;
filesystem('public')->directoryExists(string $location): bool;
filesystem('public')->exists(string $location): bool;
filesystem('public')->write(string $location, string $contents, array $config = []): FileSystemInterface;
filesystem('public')->writeStream(string $location, resource $contents, array $config = []): FileSystemInterface;
filesystem('public')->read(string $location): string;
filesystem('public')->readStream(string $location): mixed;
filesystem('public')->delete(string $location): FileSystemInterface;
filesystem('public')->createDirectory(string $location, array $config = []): FileSystemInterface;
filesystem('public')->list(string $location): mixed;
filesystem('public')->move(string $source, string $destination, array $config = []): FileSystemInterface;
filesystem('public')->copy(string $source, string $destination, array $config = []): FileSystemInterface;
filesystem('public')->fileSize(string $location): int;
filesystem('public')->mimeType(string $location): string;
filesystem('public')->url(string $location): string;
filesystem('public')->temporaryUrl(string $location, int $ttl, array $config = []): string;
```

## Filesytems

You can define more filesystems unter `config/filesytem.php`.

### storage

The default `filesystem.storage` handler to access files in the 'local' `storeage` folder.

```bash
filesystem('storage'); // returns the same as container('filesystem.storage')
```

### public

The default `filesystem.public` handler to access files in the `public` folder.
Needed envs:

- FOLDER_PERMISSIONS: `0750`
- FILE_PERMISSIONS: `0640`

### ftp

An example configuration fpr an FTP Server.
Needed envs:

- FTP_USERNAME
- FTP_PASSWORD
- FTP_HOST
- FTP_PORT: `21`
- FTP_SSL
- FTP_PASSIV: `true`
- FTP_TIMEOUT: `10`
- FTP_FOLDER_PERMISSIONS: `0750`
- FTP_FILE_PERMISSIONS: `0640`

### s3_pubic

An example configuration for a Public `AWS S3 Bucket` or `Minio Bucket`.

- AWS_ACCESS_KEY_ID: `null`
- AWS_SECRET_ACCESS_KEY: `null`
- AWS_DOMAIN: `https://minio.domain.tld`
- AWS_PUBLIC_DOMAIN: `https://cdn.domain.tld`
- AWS_REGION: `eu-central-1`
- AWS_BUCKET_PUBLIC: `bucket`
- AWS_PUBLIC_ROOT: `null`
- AWS_USE_PATH_STYLE_ENDPOINT: `true` for Minio S3

### s3_private

An example configuration for a Private `AWS S3 Bucket` or `Minio Bucket`.
Needed envs:

- AWS_ACCESS_KEY_ID: `null`
- AWS_SECRET_ACCESS_KEY: `null`
- AWS_DOMAIN: `https://bucket.s3.eu-central-1.amazonaws.com`
- AWS_REGION: `eu-central-1`
- AWS_BUCKET_PRIVATE: `bucket`
- AWS_PRIVATE_ROOT: `null`
- AWS_USE_PATH_STYLE_ENDPOINT: `false` for AWS S3

### sftp

An example configuration for a `sftp` implementation over `phpN.n-ssh2`.
Needed envs:

- SFTP_USERNAME: `null`
- SFTP_PUBIC_KEY: `null`
- SFTP_PRIVATE_KEY: `null`
- SFTP_PRIVATE_KEY_PASSWORD: `null`
- SFTP_HOST: `null`
- SFTP_PORT: 22
- SFTP_FILE_PERMISSIONS': 064
- SFTP_FOLDER_PERMISSIONS': 075
