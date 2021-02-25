<?php declare(strict_types=1);

namespace TinyFramework\Logger;

interface LogRotateInterface
{

    public function rotate(): LogRotateInterface;

}
