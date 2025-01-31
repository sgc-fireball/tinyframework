<?php

declare(strict_types=1);

namespace TinyFramework\Tests\WebToken;

use PHPUnit\Framework\TestCase;
use TinyFramework\WebToken\JWS;

class JWSTest extends TestCase
{
    public function providerJws(): array
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
            [JWS::ALG_HS256, $key],
            [JWS::ALG_HS384, $key],
            [JWS::ALG_HS512, $key],
            // RS
            [JWS::ALG_RS256, $rsKey, $rsPub],
            [JWS::ALG_RS384, $rsKey, $rsPub],
            [JWS::ALG_RS512, $rsKey, $rsPub],
            // PS
            [JWS::ALG_PS256, $rsKey, $rsPub],
            [JWS::ALG_PS384, $rsKey, $rsPub],
            [JWS::ALG_PS512, $rsKey, $rsPub],
            // ES
            [JWS::ALG_ES256, $esKey, $esPub],
            [JWS::ALG_ES256K, $esKey, $esPub],
            [JWS::ALG_ES384, $esKey, $esPub],
            [JWS::ALG_ES512, $esKey, $esPub],
            // EdDSA
            [JWS::ALG_EDDSA, $edKey, $edPub],
        ];
    }

    /**
     * @dataProvider providerJws
     */
    public function testJws(string $alg, string $key, string $pub = ''): void
    {
        $random = random_bytes(mt_rand(10, 100));
        $jws = new JWS($alg, $key, $pub);
        $token = $jws->encode($random);
        $payload = $jws->decode($token);
        $this->assertEquals($random, $payload);
    }
}
