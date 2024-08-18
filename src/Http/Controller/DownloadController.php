<?php

namespace TinyFramework\Http\Controller;

use TinyFramework\Core\ContainerInterface;
use TinyFramework\Exception\HttpException;
use TinyFramework\FileSystem\LocalFileSystem;
use TinyFramework\Http\DownloadResponse;
use TinyFramework\Http\RequestInterface;
use TinyFramework\WebToken\JWT;

class DownloadController
{

    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function download(RequestInterface $request, string $fsDisk, string $fsPath): DownloadResponse
    {
        try {
            $sign = (string)($request->get('sign') ?: '');

            $jwt = new JWT(JWT::ALG_HS512, secret());
            $data = $jwt->decode($sign);
            if (!array_key_exists('file', $data) || $data['file'] !== $fsPath) {
                throw new \InvalidArgumentException('Could not found "file" information in download jwt.');
            }
            if (!array_key_exists('disk', $data) || $data['disk'] !== $fsDisk) {
                throw new \InvalidArgumentException('Could not found "disk" information in download jwt.');
            }
            $service = 'filesystem.' . $fsDisk;
            if (!$this->container->has($service)) {
                throw new \RuntimeException('Could not found '.$service.' definition.');
            }
            $fileSystem = $this->container->get($service);
            if (!($fileSystem instanceof LocalFileSystem)) {
                throw new \RuntimeException('Filesystem is not an instance of LocalFileSystem.');
            }
            if (!$fileSystem->fileExists($fsPath)) {
                throw new \RuntimeException('Could not found file on local file system.');
            }
            $filePath = $fileSystem->path($fsPath);
            return new DownloadResponse($filePath);
        } catch (\Throwable $e) {
            usleep(mt_rand(100_000, 500_000));
            throw new HttpException('Not found: '. $e->getMessage(), 404);
        }
    }

}
