<?php declare(strict_types=1);

namespace TinyFramework\Logger;

class FileLogger extends LoggerAwesome implements LoggerInterface
{

    private string $path;

    public function __construct(array $config)
    {
        if (!is_dir($config['path'])) {
            if (!mkdir($config['path'], 0770, true)) {
                throw new \RuntimeException('Could not create logs folder.');
            }
        }
        if (is_dir($config['path']) && is_writable($config['path'])) {
            $this->path = $config['path'];
            return;
        }
        throw new \RuntimeException('Missing logs folder.');
    }

    public function log(string $level, string $message, array $context = []): static
    {
        $message = sprintf(
            '[%s|%s] %s', date('Y-m-d H:i:s'),
            $level,
            $this->buildMessage($level, $message, $context)
        );

        $file = sprintf('%s/%s.log', $this->path, date('Y-m-d'));
        $bytes = file_put_contents($file, trim($message) . "\n", file_exists($file) ? FILE_APPEND : 0);
        if ($bytes === false) {
            trigger_error('Could not write log message.', E_USER_ERROR);
        }
        return $this;
    }

}
