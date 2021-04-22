# Logger

- [Introduction](#introduction)

## Introduction

```php
use TinyFramework\Mail\Mail;

mailer()->send(
    Mail::create()
        ->to('example@domain.tld', 'Example')
        ->subject('my subject')
        ->text('My plaintext message')
        ->html('<p>My HTML message</p>')
        ->priority(1)
);
```
