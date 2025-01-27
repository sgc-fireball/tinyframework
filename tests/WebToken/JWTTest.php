<?php

declare(strict_types=1);

namespace TinyFramework\Tests\WebToken;

use PHPUnit\Framework\TestCase;
use TinyFramework\WebToken\JWT;

class JWTTest extends TestCase
{
    public function providerJwt(): array
    {
        $key = random_bytes(16);
        $rsPub = realpath(__DIR__ . '/example.rsa4096.crt.pem');
        $rsKey = realpath(__DIR__ . '/example.rsa4096.key.pem');
        $esPub = realpath(__DIR__ . '/example.ecp256.crt.pem');
        $esKey = realpath(__DIR__ . '/example.ecp256.key.pem');
        $edPub = realpath(__DIR__ . '/example.ed25519.crt.pem');
        $edKey = realpath(__DIR__ . '/example.ed25519.key.pem');
        return [
            // HS
            [JWT::ALG_HS256, $key],
            [JWT::ALG_HS384, $key],
            [JWT::ALG_HS512, $key],
            // RS
            [JWT::ALG_RS256, $rsKey, $rsPub],
            [JWT::ALG_RS384, $rsKey, $rsPub],
            [JWT::ALG_RS512, $rsKey, $rsPub],
            // PS
            [JWT::ALG_PS256, $rsKey, $rsPub],
            [JWT::ALG_PS384, $rsKey, $rsPub],
            [JWT::ALG_PS512, $rsKey, $rsPub],
            // ES
            [JWT::ALG_ES256, $esKey, $esPub],
            [JWT::ALG_ES256K, $esKey, $esPub],
            [JWT::ALG_ES384, $esKey, $esPub],
            [JWT::ALG_ES512, $esKey, $esPub],
            // EdDSA
            [JWT::ALG_EDDSA, $edKey, $edPub],
        ];
    }

    /**
     * @dataProvider providerJwt
     */
    public function testJwt(string $alg, string $key, string $pub = ''): void
    {
        $time = time();
        $jwt = new JWT($alg, $key, $pub);
        $jwt->time($time - 1);
        $jwt->expirationTime($time + 1);
        $jwt->subject($subject = (string)random_int(1, 256));
        $jwt->audience($audience = (string)random_int(1, 256));
        $jwt->issuer($issuer = (string)random_int(1, 256));
        $jwt->id($id = md5((string)random_int(1, 256)));
        $token = $jwt->encode(['value' => $time]);
        $payload = $jwt->decode($token);
        $this->assertEquals($time, $payload['value']);
        $this->assertEquals($subject, $payload['sub']);
        $this->assertEquals($audience, $payload['aud']);
        $this->assertEquals($issuer, $payload['iss']);
        $this->assertEquals($id, $payload['jti']);
    }
}
