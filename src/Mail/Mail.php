<?php declare(strict_types=1);

namespace TinyFramework\Mail;

use RuntimeException;

class Mail
{

    const PRIORITY_HIGHEST = 1;
    const PRIORITY_HIGH = 2;
    const PRIORITY_NORMAL = 3;
    const PRIORITY_LOW = 4;
    const PRIORITY_LOWEST = 5;

    private array $header = [];

    private ?string $sender = null;

    private ?string $returnPath = null;

    private ?string $replyTo = null;

    private array $from = [];

    private array $to = [];

    private array $cc = [];

    private array $bcc = [];

    private int $priority = 3;

    private ?string $subject = null;

    private ?string $text = null;

    private ?string $html = null;

    private array $attachments = [];

    public static function create(): Mail
    {
        return new self();
    }

    public function header(string $key = null, array|string $value = null, bool $replace = true): static|array
    {
        if (is_null($key)) {
            return $this->header;
        }
        if (is_null($value)) {
            return $this->header[$key] ?? [];
        }
        $value = is_array($value) ? $value : [$value];
        if ($replace) {
            $this->header[$key] = $value;
        } else {
            $this->header[$key] = array_merge($this->header[$key], $value);
        }
        return $this;
    }

    public function sender(string $email = null): static|string|null
    {
        if (is_null($email)) {
            return $this->sender;
        }
        $this->sender = $email;
        return $this;
    }

    public function returnPath(string $email = null): static|string|null
    {
        if (is_null($email)) {
            return $this->returnPath;
        }
        $this->returnPath = $email;
        return $this;
    }

    public function replyTo(string $email = null): static|string|null
    {
        if (is_null($email)) {
            return $this->replyTo;
        }
        $this->replyTo = $email;
        return $this;
    }

    public function from(string $email = null, string $name = null): static|array
    {
        if (is_null($email)) {
            return $this->from;
        }
        if (is_null($this->sender)) {
            $this->sender = $email;
        }
        if (is_null($this->returnPath)) {
            $this->returnPath = $email;
        }
        $this->from = ['email' => $email, 'name' => $name ?? $email];
        return $this;
    }

    public function to(string $email = null, string $name = null): static|array
    {
        if (is_null($email)) {
            return $this->to;
        }
        $this->to[] = ['email' => $email, 'name' => $name ?? $email];
        return $this;
    }

    public function cc(string $email = null, string $name = null): static|array
    {
        if (is_null($email)) {
            return $this->cc;
        }
        $this->cc[] = ['email' => $email, 'name' => $name ?? $email];
        return $this;
    }

    public function bcc(string $email = null, string $name = null): static|array
    {
        if (is_null($email)) {
            return $this->bcc;
        }
        $this->bcc[] = ['email' => $email, 'name' => $name ?? $email];
        return $this;
    }

    public function priority(int $priority = null): static|int
    {
        if (is_null($priority)) {
            return $this->priority;
        }
        $this->priority = min(max(1, (int)$priority), 5);
        return $this;
    }

    public function subject(string $subject = null): static|string|null
    {
        if (is_null($subject)) {
            return $this->subject;
        }
        $this->subject = $subject;
        return $this;
    }

    public function text(string $text = null): static|string|null
    {
        if (is_null($text)) {
            if (is_null($this->text)) {
                $text = str_replace(["\r", "\n"], '', (string)$this->html);
                $text = str_replace(['<br>', '<br/>', '<br />'], "\n", $text);
                return strip_tags($text);
            }
            return $this->text;
        }
        $this->text = $text;
        return $this;
    }

    public function html(string $html = null): static|string|null
    {
        if (is_null($html)) {
            if (is_null($this->html)) {
                return nl2br((string)$this->text);
            }
            return $this->html;
        }
        $this->html = $html;
        return $this;
    }

    public function attachments(): array
    {
        return $this->attachments;
    }

    public function attachmentFile(string $path, string $filename = null, string $mimeType = null): static
    {
        if (!file_exists($path)) {
            throw new RuntimeException('File not found.');
        }
        $this->attachments[] = [
            'path' => $path,
            'filename' => $filename ?? basename($path),
            'mimetype' => $mimeType ?? mime_content_type($path) ?? 'application/octet-stream'
        ];
        return $this;
    }

    public function attachmentBody(string $content, string $filename, string $mimeType = 'application/octet-stream'): static
    {
        $this->attachments[] = [
            'content' => $content,
            'filename' => $filename,
            'mimetype' => $mimeType
        ];
        return $this;
    }

}
