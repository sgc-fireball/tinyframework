# Crypt

- [Introduction](#introduction)

## Introduction
```php
$encrypted = crypto()->encrypt($plaintext, $key = null); // ciphertext
$decrypted = crypto()->decrypt($encrypted, $key = null); // plaintext
assert($decrypted === $plaintext, 'Check plaintext.');
```
