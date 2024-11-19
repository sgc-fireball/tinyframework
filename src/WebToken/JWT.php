<?php

declare(strict_types=1);

namespace TinyFramework\WebToken;

use OpenSSLAsymmetricKey;
use RuntimeException;

class JWT
{
    private const ASN1_INTEGER = 0x02;
    private const ASN1_SEQUENCE = 0x10;
    private const ASN1_BIT_STRING = 0x03;

    /**
     * from okay to safer
     */
    public const ALG_HS256 = 'HS256'; // shared secret
    public const ALG_HS384 = 'HS384'; // shared secret
    public const ALG_HS512 = 'HS512'; // shared secret
    public const ALG_RS256 = 'RS256'; // RSASSA PKCS1 v1.5 signature with SHA-256
    public const ALG_RS384 = 'RS384'; // RSASSA PKCS1 v1.5 signature with SHA-384
    public const ALG_RS512 = 'RS512'; // RSASSA PKCS1 v1.5 signature with SHA-512
    #public const ALG_PS256 = 'PS256'; // RSASSA-PSS signature with SHA-256
    #public const ALG_PS384 = 'PS384'; // RSASSA-PSS signature with SHA-384
    #public const ALG_PS512 = 'PS512'; // RSASSA-PSS signature with SHA-512
    public const ALG_ES256 = 'ES256'; // ECDSA using secp256r1 signature with SHA-256
    public const ALG_ES256K = 'ES256K'; // ECDSA using secp256k1 signature with SHA-256
    public const ALG_ES384 = 'ES384'; // ECDSA using secp384r1 signature with SHA-384
    public const ALG_ES512 = 'ES512'; // ECDSA using secp521r1 signature with SHA-512
    public const ALG_EDDSA = 'EdDSA'; // EdDSA using Ed25519

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
        if (str_starts_with($this->alg, 'HS')) {
            $this->public = $this->private;
        }
        if (str_starts_with($this->alg, 'RS') || str_starts_with($this->alg, 'ES')) {
            if (is_string($this->private)) {
                if (file_exists($this->private)) {
                    $this->private = 'file://' . $this->private;
                }
                $this->private = openssl_pkey_get_private($this->private);
            }
            if (is_string($this->public)) {
                if (file_exists($this->public)) {
                    $this->public = 'file://' . $this->public;
                }
                $this->public = openssl_pkey_get_public($this->public);
            }
        }
        if ($this->alg === 'EdDSA') {
            if (!extension_loaded('sodium')) {
                throw new RuntimeException('Please install php-sodium first to use EdDSA.');
            }
            $this->private = file_exists($this->private) ? file_get_contents($this->private) : $this->private;
            $this->public = file_exists($this->public) ? file_get_contents($this->public) : $this->public;
            $this->private = base64_decode($this->private, true);
            $this->public = base64_decode($this->public, true);
        }
    }

    private function typToSha(string $typ): string
    {
        return 'SHA' . preg_replace('/[^0-9]/', '', $typ);
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

    public function encode(array $payload = [], array $header = []): string
    {
        $header['typ'] = 'JWT';
        $header['alg'] = $this->alg;
        $header = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
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
        $payload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        $body = $header . '.' . $payload;

        $sign = '';
        if (str_starts_with($this->alg, 'RS') || str_starts_with($this->alg, 'ES')) {
            $success = openssl_sign($body, $sign, $this->private, $this->typToSha($this->alg));
            if ($success !== true) {
                throw new RuntimeException('OpenSSL unable to sign data: ' . openssl_error_string());
            }
            if (in_array($this->alg, ['ES256', 'ES256K'])) {
                $sign = $this->signatureFromDER($sign, 256);
            } elseif ($this->alg === 'ES384') {
                $sign = $this->signatureFromDER($sign, 384);
            } elseif ($this->alg === 'ES512') {
                $sign = $this->signatureFromDER($sign, 512);
            }
        } elseif (str_starts_with($this->alg, 'HS')) {
            $sign = hash_hmac($this->typToSha($this->alg), $body, $this->private, true);
        } elseif ($this->alg === 'EdDSA') {
            $sign = sodium_crypto_sign_detached($body, (string)$this->private);
        }

        return $body . '.' . $this->base64UrlEncode($sign);
    }

    public function decode(#[\SensitiveParameter] string $jwt, bool $verify = true): array
    {
        if ($verify && !$this->verify($jwt, true)) {
            throw new RuntimeException('Invalid jwt sign!');
        }
        $payload64 = explode('.', $jwt, 3)[1];
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
        $components = explode('.', $jwt);
        if (count($components) !== 3) {
            if ($throw) {
                throw new RuntimeException('Invalid JWT. Invalid components count.');
            }
            return false;
        }
        [$header, $payload, $sign] = $components;
        $body = $header . '.' . $payload;
        try {
            $header = (array)json_decode(
                $this->base64UrlDecode($header),
                true,
                512,
                JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING
            );
            $payload = (array)json_decode(
                $this->base64UrlDecode($payload),
                true,
                512,
                JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING
            );
            $sign = $this->base64UrlDecode($sign);
            if (!$sign) {
                throw new \RuntimeException('Invalid JWT. Unable to decode sign.');
            }
        } catch (\Throwable $e) {
            if ($throw) {
                throw new RuntimeException('Invalid JWT. Unable to decode components.');
            }
            return false;
        }
        if (!array_key_exists('typ', $header) || $header['typ'] !== 'JWT') {
            if ($throw) {
                throw new RuntimeException('Invalid JWT. Invalid typ.');
            }
            return false;
        }
        if (!array_key_exists('alg', $header) || $header['alg'] !== $this->alg) {
            if ($throw) {
                throw new RuntimeException('Invalid JWT. Invalid alg.');
            }
            return false;
        }
        if (array_key_exists('nbf', $payload) && $payload['nbf'] > $this->time) {
            if ($throw) {
                throw new RuntimeException('Invalid JWT. Not yet valid (1).');
            }
            return false;
        }
        if (array_key_exists('iat', $payload) && $payload['iat'] > $this->time) {
            if ($throw) {
                throw new RuntimeException('Invalid JWT. Not yet valid (2).');
            }
            return false;
        }
        if (array_key_exists('exp', $payload) && $payload['exp'] < $this->time) {
            if ($throw) {
                throw new RuntimeException('Invalid JWT. Token expired.');
            }
            return false;
        }

        $success = false;
        if (str_starts_with($this->alg, 'RS') || str_starts_with($this->alg, 'ES')) {
            if (str_starts_with($this->alg, 'ES')) {
                $sign = $this->signatureToDER($sign);
            }
            $success = openssl_verify($body, $sign, $this->public, $this->typToSha($this->alg)) === 1;
        } elseif (str_starts_with($this->alg, 'HS')) {
            $success = hash_equals($sign, hash_hmac($this->typToSha($this->alg), $body, $this->public, true));
        } elseif ($this->alg === 'EdDSA') {
            $success = sodium_crypto_sign_verify_detached($sign, $body, (string)$this->public);
        }
        if (!$success && $throw) {
            throw new RuntimeException('Invalid JWT. Invalid sign.');
        }
        return $success;
    }

    private function base64UrlEncode(#[\SensitiveParameter] string $input): string
    {
        return str_replace('=', '', strtr(\base64_encode($input), '+/', '-_'));
    }

    private function base64UrlDecode(#[\SensitiveParameter] string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(\strtr($input, '-_', '+/'), true);
    }

    private function signatureToDER(string $sig): string
    {
        // Separate the signature into r-value and s-value
        $length = max(1, (int)(strlen($sig) / 2));
        [$r, $s] = str_split($sig, $length);

        // Trim leading zeros
        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");

        // Convert r-value and s-value from unsigned big-endian integers to
        // signed two's complement
        if (ord($r[0]) > 0x7f) {
            $r = "\x00" . $r;
        }
        if (ord($s[0]) > 0x7f) {
            $s = "\x00" . $s;
        }

        return $this->encodeDER(
            self::ASN1_SEQUENCE,
            $this->encodeDER(self::ASN1_INTEGER, $r) .
            $this->encodeDER(self::ASN1_INTEGER, $s)
        );
    }

    private function encodeDER(int $type, string $value): string
    {
        $tag_header = 0;
        if ($type === self::ASN1_SEQUENCE) {
            $tag_header |= 0x20;
        }
        // Type
        $der = chr($tag_header | $type);
        // Length
        $der .= chr(strlen($value));
        return $der . $value;
    }

    private function signatureFromDER(string $der, int $keySize): string
    {
        // OpenSSL returns the ECDSA signatures as a binary ASN.1 DER SEQUENCE
        [$offset, $_] = $this->decodeDER($der);
        [$offset, $r] = $this->decodeDER($der, $offset);
        [$offset, $s] = $this->decodeDER($der, $offset);
        // Convert r-value and s-value from signed two's compliment to unsigned
        // big-endian integers
        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");
        // Pad out r and s so that they are $keySize bits long
        $r = str_pad($r, $keySize / 8, "\x00", STR_PAD_LEFT);
        $s = str_pad($s, $keySize / 8, "\x00", STR_PAD_LEFT);
        return $r . $s;
    }

    private function decodeDER(string $der, int $offset = 0): array
    {
        $pos = $offset;
        $size = strlen($der);
        $constructed = (ord($der[$pos]) >> 5) & 0x01;
        $type = ord($der[$pos++]) & 0x1f;
        // Length
        $len = ord($der[$pos++]);
        if ($len & 0x80) {
            $n = $len & 0x1f;
            $len = 0;
            while ($n-- && $pos < $size) {
                $len = ($len << 8) | ord($der[$pos++]);
            }
        }
        // Value
        if ($type === self::ASN1_BIT_STRING) {
            $pos++; // Skip the first contents octet (padding indicator)
            $data = substr($der, $pos, $len - 1);
            $pos += $len - 1;
        } elseif (!$constructed) {
            $data = substr($der, $pos, $len);
            $pos += $len;
        } else {
            $data = null;
        }

        return [$pos, $data];
    }
}
