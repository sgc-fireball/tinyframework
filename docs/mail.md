# Logger

- [Introduction](#introduction)

## Introduction
```php
/** @var \TinyFramework\Mail\Mailer $mailer */
$mailer = container('mailer');
$mailer->send(
    (new \TinyFramework\Mail\Mail())
        ->to('example@domain.tld', 'Example')
        ->subject('my subject')
        ->text('My plaintext message')
        ->html('<p>My HTML message</p>')
        ->priority(1)
);
```
