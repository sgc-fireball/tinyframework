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
        $root = rtrim(defined('ROOT') ? ROOT : getcwd(), DIRECTORY_SEPARATOR);
        $this->historyFile = $root . '/storage/shell/.php_history';
        $this->prompt = $prompt;
        $this->matchers = $matchers;
        readline_completion_function([&$this, 'autocomplete']);
    }

    public function readHistory(): Readline
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

    public function prompt(string $prompt = null): ?string
    {
        $prompt = trim($prompt);
        $prompt = $prompt ?? $this->prompt;
        $prompt = $prompt ? $prompt . ' ' : '';
        return readline($prompt);
    }

    public function addHistory(string $command): Readline
    {
        if ($command) {
            readline_add_history($command);
        }
        return $this;
    }

    public function saveHistory(): Readline
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
        file_put_contents(
            $this->historyFile,
            "_HiStOrY_V2_\n" . implode("\n", $history)
        );
        chmod($this->historyFile, 0600);
        return $this;
    }

    /**
     * @internal
     */
    public function autocomplete(string $input, int $index): array
    {
        $info = readline_info();
        $matches = [];
        /** @var TabCompletionInterface $matchers */
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
