# Logger

- [Introduction](#introduction)

## Introduction

At these time, this framework supports:

- SMTP
    - SSL with TLS1.2 and higher
    - STARTTLS with TLS1.2 and higher
    - AUTH PLAIN

```php
use TinyFramework\Mail\Mail;

mailer()->send(
    Mail::create()
        ->from('example@domain.tld', 'Example')
        ->to('example@domain.tld', 'Example')
        ->subject('my subject')
        ->header('X-Header', 'my value')
        ->text('My plaintext message')
        ->html('<p>My HTML message</p>')
        ->attachmentFile('/path/to/file.txt', 'export.txt', 'text/plain')
        ->priority(Mail::PRIORITY_HIGHEST)
);
```
