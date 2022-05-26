<?php

declare(strict_types=1);

namespace TinyFramework\Logger;

use RuntimeException;

class FileLogger extends LoggerAwesome implements LoggerInterface
{
    private string $path;

    public function __construct(array $config)
    {
        if (!is_dir($config['path'])) {
            if (!mkdir($config['path'], 0750, true)) {
                throw new RuntimeException('Could not create logs folder.');
            }
        }
        if (is_dir($config['path']) && is_writable($config['path'])) {
            $this->path = $config['path'];
            return;
        }
        throw new RuntimeException('Missing logs folder.');
    }

    public function log(string $level, string $message, array $context = []): static
    {
        $message = sprintf(
            '[%s|%s] %s %s',
            date('Y-m-d H:i:s'),
            $level,
            $this->buildMessage($level, $message, $context),
            json_encode($context)
        );

        $file = sprintf('%s/%s.log', $this->path, date('Y-m-d'));
        if (!file_exists($file)) {
            if (!touch($file)) {
                trigger_error('Could not create logfile.', E_USER_WARNING);
            }
            if (!chmod($file, 0640)) {
                trigger_error('Could not set chmod on logfile.', E_USER_WARNING);
            }
        }
        $bytes = file_put_contents($file, trim($message) . "\n", FILE_APPEND);
        if ($bytes === false) {
            trigger_error('Could not write log message.', E_USER_WARNING);
        }
        return $this;
    }
}
