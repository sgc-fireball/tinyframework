<?php declare(strict_types=1);

namespace TinyFramework\Shell;

use TinyFramework\Shell\TabCompletion\TabCompletionInterface;

class Readline
{

    private string $prompt;

    private string $historyFile;

    /**
     * @var TabCompletionInterface[]
     */
    private array $matchers = [];

    public function __construct(string $prompt = '$', array $matchers = [])
    {
        $this->historyFile = root_dir() . '/storage/shell/.php_history';
        $this->prompt = $prompt;
        $this->matchers = $matchers;
        readline_completion_function([&$this, 'autocomplete']);
        readline_info('readline_name', 'tinyframework');
    }

    public function setContext(?Context $context = null): static
    {
        foreach ($this->matchers as $matcher) {
            $matcher->setContext($context);
        }
        return $this;
    }

    public function readHistory(): static
    {
        if (file_exists($this->historyFile)) {
            if (!is_readable($this->historyFile)) {
                trigger_error('Could not read history file.', E_USER_WARNING);
                return $this;
            }
            readline_read_history($this->historyFile);
        }
        return $this;
    }

    /**
     * @param string $prompt
     * @return string
     */
    public function prompt(string $prompt = ''): string
    {
        $prompt = trim($prompt);
        $prompt = $prompt ?? $this->prompt;
        $prompt = $prompt ? $prompt . ' ' : '';
        return (string)readline($prompt);
    }

    public function addHistory(string $command): static
    {
        if ($command) {
            readline_add_history($command);
        }
        return $this;
    }

    public function saveHistory(): static
    {
        $dir = dirname($this->historyFile);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0700, true)) {
                trigger_error('Could not create history dir.', E_USER_WARNING);
                return $this;
            }
        }
        if (!is_writeable($dir)) {
            trigger_error('Could not writeable to history dir.', E_USER_WARNING);
            return $this;
        }
        $history = array_reverse(readline_list_history());
        $history = array_reverse(array_unique($history, SORT_STRING));
        if (array_key_exists('HISTSIZE', $_SERVER)) {
            $history = array_slice($history, count($history) - $_SERVER['HISTSIZE']);
        }
        $content = "_HiStOrY_V2_\n" . implode("\n", $history);
        $content = str_replace("\\", "\\\\", $content);
        file_put_contents($this->historyFile, $content);
        chmod($this->historyFile, 0600);
        return $this;
    }

    /**
     * @internal
     */
    public function autocomplete(string $input, int $index): array
    {
        $info = (array)readline_info();
        $matches = [];
        /** @var TabCompletionInterface $matcher */
        foreach ($this->matchers as $matcher) {
            $matches = array_merge(
                $matches,
                array_values($matcher->getMatches($info, $input, $index))
            );
        }
        return !empty($matches) ? array_unique($matches) : [''];
    }

    public function __destruct()
    {
        if (function_exists('readline_callback_handler_remove')) {
            readline_callback_handler_remove();
        }
        $this->saveHistory();
    }

}
