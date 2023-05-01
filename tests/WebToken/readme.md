# OpenSSL

# RSA4096

```bash
openssl genpkey -algorithm RSA -out example.rsa4096.key.pem -pkeyopt rsa_keygen_bits:4096
openssl rsa -in example.rsa4096.key.pem -pubout -out example.rsa4096.crt.pem
```

# ES256

```bash
openssl ecparam -genkey -name prime256v1 -noout -out example.ec256.key.pem
openssl ec -in example.ec256.key.pem -pubout -out example.ec256.crt.pem
openssl ec -in example.ec256.key.pem -pubout -outform DER | tail -c 65 | base64 | tr -d '=' | tr '/+' '-_' | tr -d "\n" > example.ec256.vapid.pub 
```

# ED25519

```php
<?php
$keypair = sodium_crypto_sign_keypair();
file_put_contents('example.ed25519.key.pem', base64_encode(sodium_crypto_sign_secretkey($keypair)));
file_put_contents('example.ed25519.crt.pem', base64_encode(sodium_crypto_sign_publickey($keypair)));
```
