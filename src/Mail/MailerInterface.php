<?php declare(strict_types=1);

namespace TinyFramework\Mail;

interface MailerInterface
{

    public function send(Mail $mail): bool;

}
