<?php

declare(strict_types=1);

namespace TinyFramework\WebToken;

use OpenSSLAsymmetricKey;
use RuntimeException;

class JWS
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

    public const ALG_PS256 = 'PS256'; // RSASSA-PSS signature with SHA-256
    public const ALG_PS384 = 'PS384'; // RSASSA-PSS signature with SHA-384
    public const ALG_PS512 = 'PS512'; // RSASSA-PSS signature with SHA-512

    public const ALG_ES256 = 'ES256'; // ECDSA using secp256r1 signature with SHA-256
    public const ALG_ES256K = 'ES256K'; // ECDSA using secp256k1 signature with SHA-256
    public const ALG_ES384 = 'ES384'; // ECDSA using secp384r1 signature with SHA-384
    public const ALG_ES512 = 'ES512'; // ECDSA using secp521r1 signature with SHA-512
    public const ALG_EDDSA = 'EdDSA'; // EdDSA using Ed25519

    protected string $typ = 'JWS';

    public function __construct(
        private string $alg,
        #[\SensitiveParameter] private OpenSSLAsymmetricKey|string $private,
        private OpenSSLAsymmetricKey|string $public = ''
    ) {
        if (str_starts_with($this->alg, 'HS')) {
            $this->public = $this->private;
        }

        if (in_array(substr($this->alg, 0, 2), ['RS', 'ES', 'PS'])) {
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

    public function encode(string|array $payload, array $header = []): string
    {
        $payload = is_array($payload) ? json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) : $payload;

        $header['typ'] = $this->typ;
        $header['alg'] = $this->alg;
        $header = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        $payload = $this->base64UrlEncode($payload);
        $body = $header . '.' . $payload;

        $sign = '';
        if (str_starts_with($this->alg, 'PS')) {
            $sign = $this->signatureRSASSAPSS($body);
        } elseif (str_starts_with($this->alg, 'RS') || str_starts_with($this->alg, 'ES')) {
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

        if (empty($sign)) {
            throw new RuntimeException('Unable to sign data.');
        }
        return $body . '.' . $this->base64UrlEncode($sign);
    }

    /**
     * JWS response a string!
     * @param string $token
     * @param bool $verify
     * @return string|array
     */
    public function decode(#[\SensitiveParameter] string $token, bool $verify = true): string|array
    {
        if ($verify && !$this->verify($token, true)) {
            throw new RuntimeException('Invalid ' . $this->typ . ' sign!');
        }
        return $this->base64UrlDecode(explode('.', $token, 3)[1]);
    }

    public function verify(#[\SensitiveParameter] string $jws, bool $throw = false): bool
    {
        $components = explode('.', $jws);
        if (count($components) !== 3) {
            if ($throw) {
                throw new RuntimeException('Invalid ' . $this->typ . '. Invalid components count.');
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
            $payload = $this->base64UrlDecode($payload);
            if (!is_string($payload)) {
                throw new \RuntimeException('Invalid ' . $this->typ . '. Invalid payload.');
            }
            $sign = $this->base64UrlDecode($sign);
            if (!$sign) {
                throw new \RuntimeException('Invalid ' . $this->typ . '. Unable to decode sign.');
            }
        } catch (\Throwable $e) {
            if ($throw) {
                throw new RuntimeException('Invalid ' . $this->typ . '. Unable to decode components.');
            }
            return false;
        }
        if (!array_key_exists('typ', $header) || $header['typ'] !== $this->typ) {
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

        $success = false;
        if (str_starts_with($this->alg, 'PS')) {
            $success = $this->verifyRSASSAPSS($body, $sign);
        } else {
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
        }
        if (!$success && $throw) {
            throw new RuntimeException('Invalid ' . $this->typ . '. Invalid sign.');
        }
        return $success;
    }

    protected function base64UrlEncode(#[\SensitiveParameter] string $input): string
    {
        return rtrim(strtr(\base64_encode($input), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(#[\SensitiveParameter] string $input): string
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

    private function signatureRSASSAPSS(string $data): string
    {
        if (!str_starts_with($this->alg, 'PS')) {
            throw new \RuntimeException('The RSASSA-PSS verify process was called with an unsupported algorithm.');
        }
        if (!function_exists('exec')) {
            throw new \RuntimeException('Please allow the function exec to execute openssl command!');
        }
        if (!command_exists('openssl')) {
            throw new \RuntimeException(
                'Please install openssl cli first. OpenSSL is needed, because PHP could not set rsa padding mode to pss.'
            );
        }

        $hashFile = tempnam(sys_get_temp_dir(), 'hash');
        $signFile = tempnam(sys_get_temp_dir(), 'sig');
        $keyFile = tempnam(sys_get_temp_dir(), 'key');
        chmod($hashFile, 0600);
        chmod($signFile, 0600);
        chmod($keyFile, 0600);

        $hash = hash($this->typToSha($this->alg), $data, true);
        file_put_contents($hashFile, $hash);
        openssl_pkey_export_to_file($this->private, $keyFile);

        $command = sprintf(
            'openssl pkeyutl -sign -in %s -out %s -inkey %s -pkeyopt digest:%s -pkeyopt rsa_padding_mode:pss --pkeyopt rsa_pss_saltlen:-1',
            escapeshellarg($hashFile),
            escapeshellarg($signFile),
            escapeshellarg($keyFile),
            escapeshellarg(strtolower($this->typToSha($this->alg)))
        );
        exec($command, $output, $result_code);
        // overwrite key file! to erase the storage
        file_put_contents($keyFile, random_bytes(4096));
        unlink($keyFile);

        $sign = file_get_contents($signFile);
        unlink($signFile);
        unlink($hashFile);

        if ($result_code === 0) {
            return $sign;
        }
        return '';
    }

    private function verifyRSASSAPSS(string $data, string $sign): bool
    {
        if (!str_starts_with($this->alg, 'PS')) {
            throw new \RuntimeException('The RSASSA-PSS verify process was called with an unsupported algorithm.');
        }
        if (!function_exists('exec')) {
            throw new \RuntimeException('Please allow the function exec to execute openssl command!');
        }
        if (!command_exists('openssl')) {
            throw new \RuntimeException(
                'Please install openssl cli first. OpenSSL is needed, because PHP could not set rsa padding mode to pss.'
            );
        }

        $hashFile = tempnam(sys_get_temp_dir(), 'hash');
        $signFile = tempnam(sys_get_temp_dir(), 'sig');
        $certFile = tempnam(sys_get_temp_dir(), 'sig');
        chmod($hashFile, 0600);
        chmod($signFile, 0600);
        chmod($certFile, 0600);

        $hash = hash($this->typToSha($this->alg), $data, true);
        file_put_contents($hashFile, $hash);
        file_put_contents($signFile, $sign);

        $key = openssl_pkey_get_details($this->public);
        file_put_contents($certFile, $key['key']);

        $command = sprintf(
            'openssl pkeyutl -verify -in %s -sigfile %s -pubin -inkey %s -pkeyopt digest:%s -pkeyopt rsa_padding_mode:pss --pkeyopt rsa_pss_saltlen:-1',
            escapeshellarg($hashFile),
            escapeshellarg($signFile),
            escapeshellarg($certFile),
            escapeshellarg(strtolower($this->typToSha($this->alg)))
        );
        exec($command, $output, $result_code);

        file_put_contents($certFile, random_bytes(4096));
        unlink($certFile);
        unlink($signFile);
        unlink($hashFile);
        return $result_code === 0;
    }

}
