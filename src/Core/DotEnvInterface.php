<?php declare(strict_types=1);

namespace TinyFramework\Core;

interface DotEnvInterface
{

    public static function instance(): DotEnvInterface;

    public function load(string $file): DotEnvInterface;

}
