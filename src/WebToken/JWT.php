<?php

declare(strict_types=1);

namespace TinyFramework\WebToken;

use OpenSSLAsymmetricKey;
use RuntimeException;

class JWT extends JWS
{

    protected string $typ = 'JWT';
    private int $time;
    private ?int $expirationTime = null;
    private ?string $audience = null;
    private ?string $issuer = null;
    private ?string $subject = null;
    private ?string $jti = null;

    public function __construct(
        private string $alg,
        #[\SensitiveParameter] private OpenSSLAsymmetricKey|string $private,
        private OpenSSLAsymmetricKey|string $public = ''
    ) {
        $this->time = time() - 1;
        parent::__construct($this->alg, $this->private, $this->public);
    }

    public function time(int $time = null): JWT
    {
        $this->time = $time;
        return $this;
    }

    public function expirationTime(int $expirationTime = null): JWT
    {
        $this->expirationTime = $expirationTime;
        return $this;
    }

    public function audience(string $audience = null): JWT
    {
        $this->audience = $audience;
        return $this;
    }

    public function issuer(string $issuer = null): JWT
    {
        $this->issuer = $issuer;
        return $this;
    }

    public function subject(string $subject = null): JWT
    {
        $this->subject = $subject;
        return $this;
    }

    public function id(string $jti = null): JWT
    {
        $this->jti = $jti;
        return $this;
    }

    /**
     * JWT returns an array. If you need a string, please use a JWS.
     * @param array|string $payload
     * @param array $header
     * @return string|array
     * @throws \JsonException
     */
    public function encode(string|array $payload, array $header = []): string
    {
        if (is_string($payload)) {
            throw new \InvalidArgumentException(
                'JWT::encode must be called with an array! If you need to sign a string, please use JWS.'
            );
        }

        $payload['iat'] = $this->time;
        $payload['nbf'] ??= $this->time;
        if ($this->audience) {
            $payload['aud'] ??= $this->audience;
        }
        if ($this->issuer) {
            $payload['iss'] ??= $this->issuer;
        }
        if ($this->subject) {
            $payload['sub'] ??= $this->subject;
        }
        if ($this->jti) {
            $payload['jti'] = $this->jti;
        }
        if ($this->expirationTime) {
            $payload['exp'] ??= $this->expirationTime;
        }
        $payload = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        return parent::encode($payload, $header);
    }

    public function decode(#[\SensitiveParameter] string $token, bool $verify = true): array
    {
        if ($verify && !$this->verify($token, true)) {
            throw new RuntimeException('Invalid ' . $this->typ . ' sign!');
        }
        $payload64 = explode('.', $token, 3)[1];
        $payload = (array)json_decode(
            $this->base64UrlDecode($payload64),
            true,
            512,
            JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING
        );
        return $payload;
    }

    public function verify(#[\SensitiveParameter] string $jwt, bool $throw = false): bool
    {
        return parent::verify($jwt, $throw);
        $payload = explode('.', $jwt)[1];
        try {
            $payload = (array)json_decode(
                $this->base64UrlDecode($payload),
                true,
                512,
                JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING
            );
        } catch (\Throwable $e) {
            if ($throw) {
                throw new RuntimeException('Invalid ' . $this->typ . '. Unable to decode components.');
            }
            return false;
        }
        if (!array_key_exists('typ', $header) || $header['typ'] !== 'JWT') {
            if ($throw) {
                throw new RuntimeException('Invalid ' . $this->typ . '. Invalid typ.');
            }
            return false;
        }
        if (!array_key_exists('alg', $header) || $header['alg'] !== $this->alg) {
            if ($throw) {
                throw new RuntimeException('Invalid ' . $this->typ . '. Invalid alg.');
            }
            return false;
        }
        if (array_key_exists('nbf', $payload) && $payload['nbf'] > $this->time) {
            if ($throw) {
                throw new RuntimeException('Invalid ' . $this->typ . '. Not yet valid (1).');
            }
            return false;
        }
        if (array_key_exists('iat', $payload) && $payload['iat'] > $this->time) {
            if ($throw) {
                throw new RuntimeException('Invalid ' . $this->typ . '. Not yet valid (2).');
            }
            return false;
        }
        if (array_key_exists('exp', $payload) && $payload['exp'] < $this->time) {
            if ($throw) {
                throw new RuntimeException('Invalid ' . $this->typ . '. Token expired.');
            }
            return false;
        }
        return true;
    }

}
