<?php declare(strict_types=1);

namespace TinyFramework\Mail;

use Swift_Mailer;
use Swift_Message;
use Swift_Attachment;

class Mailer
{

    private Swift_Mailer $mailer;

    private ?string $fromEmail;

    private ?string $fromName;

    /**
     * @internal
     */
    public function from(string $email = null, string $name = null): Mailer
    {
        $this->fromEmail = $email;
        $this->fromName = $name;
        return $this;
    }

    /**
     * @internal
     * @param Swift_Mailer|null $mailer
     * @return $this|Swift_Mailer
     */
    public function mailer(Swift_Mailer $mailer = null)
    {
        if (is_null($mailer)) {
            return $this->mailer;
        }
        $this->mailer = $mailer;
        return $this;
    }

    public function send(Mail $mail): bool
    {
        $html = $mail->html() ?? nl2br($mail->text());
        $text = $mail->text();
        if (!$text) {
            $text = str_replace(["\r", "\n"], '', $html);
            $text = str_replace(['<br>', '<br/>', '<br />'], "\n", $text);
            $text = strip_tags($text);
        }

        $message = new Swift_Message();
        foreach ($mail->header() as $key => $value) {
            $message->getHeaders()->addTextHeader($key, $value);
        }
        $message->setCharset('utf-8');
        $message->setPriority($mail->priority());
        $message->setSubject($mail->subject());
        $message->addPart($text, 'text/plain', 'utf-8');
        $message->setBody($html, 'text/html', 'utf-8');
        if (!empty($mail->from())) {
            $from = $mail->from();
            $message->setFrom([$from['email'] => $from['name'] ?? $from['email']]);
        } else if ($this->fromEmail) {
            $message->setFrom([$this->fromEmail => $this->fromName]);
        }
        if ($mail->sender()) {
            $message->setSender($mail->sender());
        }
        if ($mail->returnPath()) {
            $message->setReturnPath($mail->returnPath());
        }
        foreach ($mail->to() as $to) {
            $message->addTo($to['email'], $to['name']);
        }
        foreach ($mail->cc() as $cc) {
            $message->addCc($cc['email'], $cc['name']);
        }
        foreach ($mail->bcc() as $bcc) {
            $message->addBcc($bcc['email'], $bcc['name']);
        }
        foreach ($mail->attachments() as $attachment) {
            if (array_key_exists('path', $attachment)) {
                $message->attach(
                    Swift_Attachment::fromPath($attachment['path'])
                        ->setFilename($attachment['filename'])
                        ->setContentType($attachment['mimetype'])
                );
            } elseif (array_key_exists('content', $attachment)) {
                $message->attach(
                    (new Swift_Attachment())
                        ->setFilename($attachment['filename'])
                        ->setContentType($attachment['mimetype'])
                        ->setBody($attachment['content'])
                );
            }
        }
        return (bool)$this->mailer->send($message);
    }

}
