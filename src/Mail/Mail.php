<?php declare(strict_types=1);

namespace TinyFramework\Mail;

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

    private array $from = [];

    private array $to = [];

    private array $cc = [];

    private array $bcc = [];

    private int $priority = 3;

    private string $subject = '';

    private string $text = '';

    private string $html = '';

    private array $attachments = [];

    public function header(string $key = null, string $value = null)
    {
        if (is_null($key)) {
            return $this->header;
        }
        if (is_null($value)) {
            throw new \RuntimeException('Missing header value!');
        }
        $this->header[$key] = $value;
        return $this;
    }

    public function sender(string $email = null)
    {
        if (is_null($email)) {
            return $this->sender;
        }
        $this->sender = $email;
        return $this;
    }

    public function returnPath(string $email = null)
    {
        if (is_null($email)) {
            return $this->returnPath;
        }
        $this->returnPath = $email;
        return $this;
    }

    public function from(string $email = null, string $name = null)
    {
        if (is_null($email)) {
            return $this->from;
        }
        $this->from = ['email' => $email, 'name' => $name ?? $email];
        return $this;
    }

    /**
     * @param string|null $email
     * @param string|null $name
     * @return Mailer|array
     */
    public function to(string $email = null, string $name = null)
    {
        if (is_null($email)) {
            return $this->to;
        }
        $this->to[] = ['email' => $email, 'name' => $name ?? $email];
        return $this;
    }

    /**
     * @param string|null $email
     * @param string|null $name
     * @return Mailer|array
     */
    public function cc(string $email = null, string $name = null)
    {
        if (is_null($email)) {
            return $this->cc;
        }
        $this->cc[] = ['email' => $email, 'name' => $name ?? $email];
        return $this;
    }

    /**
     * @param string|null $email
     * @param string|null $name
     * @return Mailer|array
     */
    public function bcc(string $email = null, string $name = null)
    {
        if (is_null($email)) {
            return $this->bcc;
        }
        $this->bcc[] = ['email' => $email, 'name' => $name ?? $email];
        return $this;
    }

    public function priority(int $priority = null)
    {
        if (is_null($priority)) {
            return $this->priority;
        }
        $this->priority = min(max(0, (int)$priority), 5);
        return $this;
    }

    /**
     * @param string|null $subject
     * @return $this|string
     */
    public function subject(string $subject = null)
    {
        if (is_null($subject)) {
            return $this->subject;
        }
        $this->subject = $subject;
        return $this;
    }

    /**
     * @param string|null $text
     * @return $this|string
     */
    public function text(string $text = null)
    {
        if (is_null($text)) {
            return $this->text;
        }
        $this->text = $text;
        return $this;
    }

    /**
     * @param string|null $html
     * @return $this|string
     */
    public function html(string $html = null)
    {
        if (is_null($html)) {
            return $this->html;
        }
        $this->html = $html;
        return $this;
    }

    public function attachments(): array
    {
        return $this->attachments;
    }

    public function attachmentFile(string $path, string $filename = null, string $mimeType = null)
    {
        if (!file_exists($path)) {
            throw new \RuntimeException('File not found.');
        }
        $this->attachments = [
            'path' => $path,
            'filename' => $filename ?? basename($path),
            'mimetype' => $mimeType ?? mime_content_type($path)
        ];
        return $this;
    }

    public function attachmentBody(string $content, string $filename, string $mimeType)
    {
        $this->attachments = ['content' => $content, 'filename' => $filename, 'mimetype' => $mimeType];
        return $this;
    }

}
