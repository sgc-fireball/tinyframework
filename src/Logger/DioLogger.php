<?php

declare(strict_types=1);

namespace TinyFramework\Logger;

use RuntimeException;

class DioLogger extends LoggerAwesome implements LoggerInterface
{
    private string $path;
    private $fp;

    public function __construct(#[\SensitiveParameter] array $config)
    {
        if (!extension_loaded('dio')) {
            throw new RuntimeException('Please install ext-dio for dio logging');
        }
        $config['path'] ??= '/proc/1/fd/1';
        if (is_file($config['path']) && is_writable($config['path'])) {
            $this->path = $config['path'];
            return;
        }
        throw new RuntimeException('Missing folder.');
    }

    public function log(string $level, string $message, array $context = []): static
    {
        $message = json_encode([
            'type' => 'log',
            'host' => gethostname(),
            'time' => date('Y-m-d\TH:i:s.uP'),
            'system' => 'tinyframework',
            'level' => $level,
            'message' => $this->buildMessage($level, $message, $context),
            'context' => $context,
        ]);
        if (!$this->fp) {
            $this->fp = dio_open($this->path, O_WRONLY);
        }
        if (!$this->fp) {
            trigger_error('Could not write log message.', E_USER_WARNING);
            return $this;
        }
        $bytes = dio_write($this->fp, $message);
        if ($bytes === false) {
            trigger_error('Could not write log message.', E_USER_WARNING);
        }
        return $this;
    }

    public function __destruct()
    {
        if ($this->fp) {
            @dio_close($this->fp);
        }
    }
}
