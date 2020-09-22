<?php declare(strict_types=1);

namespace TinyFramework\Http;

class Uri
{

    private ?string $schema = null;

    private ?string$user = null;

    private ?string$pass = null;

    private ?string$host = null;

    private ?int $port = null;

    private string $path = '/';

    private ?string $query = null;

    private ?string $fragment = null;

    public function __construct(string $uri = null)
    {
        if (!empty($uri)) {
            $parts = parse_url($uri);
            $this->schema = $parts['scheme'] ?? null;
            $this->user = $parts['user'] ?? null;
            $this->pass = $parts['pass'] ?? null;
            $this->host = $parts['host'] ? ltrim($parts['host'], ':') : null;
            $this->port = $parts['port'] ?? null;
            $this->path = $parts['path'] ?? '/';
            $this->query = $parts['query'] ?? null;
            $this->fragment = $parts['fragment'] ?? null;
        }
    }

    private function clone(): self
    {
        $uri = new self();
        $uri->schema = $this->schema;
        $uri->user = $this->user;
        $uri->pass = $this->pass;
        $uri->host = $this->host;
        $uri->port = $this->port;
        $uri->path = $this->path;
        $uri->query = $this->query;
        $uri->fragment = $this->fragment;
        return $uri;
    }

    public function scheme(string $scheme = null)
    {
        if (is_null($scheme)) {
            return $this->schema;
        }
        if (preg_match('/^[a-z]([a-z0-9+.-]+)$/', $scheme)) {
            throw new \InvalidArgumentException('Invalid schema.');
        }
        $uri = $this->clone();
        $uri->schema = $scheme;
        return $uri;
    }

    public function userInfo(string $user = null, string $pass = null)
    {
        if (is_null($user)) {
            return ($this->user . ($this->pass ? ':' . $this->pass : '')) ?: null;
        }
        $uri = $this->clone();
        $uri->user = $user;
        $uri->pass = $pass;
        return $uri;
    }

    public function authority(): ?string
    {
        return (($this->user ? $this->user . '@' : '') . $this->host . ($this->port ? ':' . $this->port : '')) ?: null;
    }

    public function host(string $host = null)
    {
        if (is_null($host)) {
            return $this->host;
        }
        if (!filter_var($host, FILTER_VALIDATE_IP) && !filter_var('info@' . $host, FILTER_FLAG_HOSTNAME)) {
            throw new \InvalidArgumentException('Invalid host.');
        }
        $uri = $this->clone();
        $uri->host = $host;
        return $uri;
    }

    public function port(int $port = null)
    {
        if (is_null($port)) {
            return $this->port;
        }
        if ($port < 0 || $port > 65535) {
            throw new \InvalidArgumentException('Invalid port.');
        }
        $uri = $this->clone();
        $uri->port = (int)$port;
        return $uri;
    }

    public function path(string $path = null)
    {
        if (is_null($path)) {
            return $this->path ?: '/';
        }
        $uri = $this->clone();
        $uri->path = $path;
        return $uri;
    }

    /**
     * @param null|string|array $query
     * @return $this|mixed|string|null
     */
    public function query($query = null)
    {
        if (is_null($query)) {
            return $this->query;
        }
        $uri = $this->clone();
        $uri->query = is_array($query) ? http_build_query($query) : $query;
        return $uri;
    }

    public function fragment(string $fragment = null)
    {
        if (is_null($fragment)) {
            return $this->fragment;
        }
        $uri = $this->clone();
        $uri->fragment = $fragment;
        return $uri;
    }

    private function needPort(): bool
    {
        if ($this->schema === 'ftp' && $this->port === 21) return false;
        if ($this->schema === 'ssh' && $this->port === 22) return false;
        if ($this->schema === 'smtp' && $this->port === 25) return false;
        if ($this->schema === 'dns' && $this->port === 53) return false;
        if ($this->schema === 'http' && $this->port === 80) return false;
        if ($this->schema === 'pop3' && $this->port === 110) return false;
        if ($this->schema === 'imap' && $this->port === 143) return false;
        if ($this->schema === 'https' && $this->port === 443) return false;
        if ($this->schema === 'mysql' && $this->port === 3306) return false;
        if ($this->schema === 'imaps' && $this->port === 993) return false;
        if ($this->schema === 'pop3s' && $this->port === 995) return false;
        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $uri = '';
        $uri .= $this->schema ? $this->schema . '://' : '';
        if ($this->user) {
            $uri .= $this->user;
            $uri .= $this->pass ? ':' . $this->pass : '';
            $uri .= '@';
        }
        $uri .= $this->host;
        if ($this->needPort()) {
            $uri .= $this->port ? ':' . $this->port : '';
        }
        $uri .= $this->path ?? '/';
        $uri .= $this->query ? '?' . $this->query : '';
        $uri .= $this->fragment ? '#' . $this->fragment : '';
        return $uri;
    }

}
