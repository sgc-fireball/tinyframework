<?php

declare(strict_types=1);

namespace TinyFramework\Mail;

use InvalidArgumentException;
use RuntimeException;
use TinyFramework\Helpers\Uuid;

class SmtpMailer extends MailerAwesome implements MailerInterface
{
    private array $config = [
        'host' => 'localhost',
        'port' => 25,
        'encryption' => 'tls',
        'username' => null,
        'password' => null,
        'local_domain' => '_',
        'from_address' => null,
        'from_name' => null,
        'allow_self_signed' => false,
        'verify_peer' => true,
        'verify_peer_name' => true,
    ];

    public function __construct(#[\SensitiveParameter] array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->config['encryption'] = strtolower($this->config['encryption']);
        if ($this->config['local_domain'] === '_') {
            $this->config['local_domain'] = gethostname();
        }
    }

    public function send(Mail $mail): bool
    {
        /** @see https://tools.ietf.org/html/rfc2821 */

        $from = $mail->from();
        if (!array_key_exists('email', $from) || !$from['email']) {
            $mail->from($this->config['from_address'], $this->config['from_name']);
            $from = $mail->from();
        }
        if (!array_key_exists('email', $from) || !$from['email']) {
            throw new \RuntimeException('Missing env MAIL_FROM_ADDRESS.');
        }

        $crypto = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;

        $context = stream_context_create();
        stream_context_set_option($context, 'ssl', 'allow_self_signed', $this->config['allow_self_signed']);
        stream_context_set_option($context, 'ssl', 'verify_peer', $this->config['verify_peer']);
        stream_context_set_option($context, 'ssl', 'verify_peer_name', $this->config['verify_peer_name']);

        $fp = stream_socket_client(
            $this->config['host'] . ':' . $this->config['port'],
            $errno,
            $errstr,
            15,
            STREAM_CLIENT_CONNECT,
            $context
        );
        if (!$fp) {
            throw new RuntimeException('Could not connect to ' . $this->config['host'] . ':' . $this->config['port']);
        }
        if ($this->config['encryption'] === 'ssl') {
            stream_socket_enable_crypto($fp, true, $crypto);
        }
        $this->read($fp);
        if ($this->config['encryption'] === 'tls') {
            $this->write($fp, "STARTTLS\r\n");
            stream_socket_enable_crypto($fp, true, $crypto);
        }
        $this->write($fp, sprintf("HELO %s\r\n", idn_to_ascii($this->config['local_domain'])));

        if (\strlen((string)$this->config['username']) && \strlen((string)$this->config['password'])) {
            if (!in_array($this->config['encryption'], ['ssl', 'tls'])) {
                throw new RuntimeException('Does not support login without encryption!');
            }
            $this->write($fp, "AUTH LOGIN\r\n");
            $this->write($fp, base64_encode($this->config['username']) . "\r\n");
            $this->write($fp, base64_encode($this->config['password']) . "\r\n");
        }

        $this->write($fp, sprintf("MAIL FROM: <%s>\r\n", $this->encodeAddress($mail->from()['email'])));
        foreach ($mail->to() as $to) {
            $this->write($fp, sprintf("RCPT TO: <%s>\r\n", $this->encodeAddress($to['email'])));
        }
        $this->write($fp, "DATA\r\n");

        $this->write(
            $fp,
            sprintf("Message-ID: <%s>\r\n", $this->encodeAddress(Uuid::v6() . '@' . $this->config['local_domain'])),
            false
        );
        $this->write($fp, sprintf("Date: %s\r\n", date('r')), false);
        $this->write($fp, sprintf("User-Agent: %s\r\n", userAgent()), false);
        $this->write($fp, sprintf("Subject: %s\r\n", mb_encode_mimeheader($mail->subject(), 'UTF-8', 'Q')), false);

        if ($from = $this->compileAddressHeader([$mail->from()])) {
            $this->write($fp, "From: " . trim($from) . "\r\n", false);
        }
        if ($to = $this->compileAddressHeader($mail->to())) {
            $this->write($fp, "To: " . trim($to) . "\r\n", false);
        }
        if ($cc = $this->compileAddressHeader($mail->cc())) {
            $this->write($fp, "Cc: " . trim($cc) . "\r\n", false);
        }
        if ($bcc = $this->compileAddressHeader($mail->bcc())) {
            $this->write($fp, "Bcc: " . trim($bcc) . "\r\n", false);
        }
        if ($mail->returnPath()) {
            $this->write($fp, sprintf("Return-Path: %s\r\n", $this->encodeAddress($mail->returnPath())), false);
        }
        if ($mail->replyTo()) {
            $this->write($fp, sprintf("Reply-To: %s\r\n", $this->encodeAddress($mail->replyTo())), false);
        }
        if ($mail->priority() !== Mail::PRIORITY_NORMAL) {
            $this->write($fp, sprintf("X-Priority: %d\r\n", $mail->priority()), false);
        }
        foreach ($mail->header() as $key => $values) {
            if (\is_array($values)) {
                foreach ($values as $value) {
                    $this->write($fp, sprintf("%s: %s\r\n", $key, $value), false);
                }
            } else {
                $this->write($fp, sprintf("%s: %s\r\n", $key, $values), false);
            }
        }

        /**
         * @see https://stackoverflow.com/questions/3902455/mail-multipart-alternative-vs-multipart-mixed
         */
        $this->write($fp, "MIME-Version: 1.0\r\n", false);

        $this->write(
            $fp,
            sprintf("Content-Type: multipart/mixed; boundary=\"%s\"\r\n\r\n", $mixedBoundary = Uuid::v6()),
            false
        );
        $this->write($fp, sprintf("--%s\r\n", $mixedBoundary), false);

        $this->write(
            $fp,
            sprintf("Content-Type: multipart/related; boundary=\"%s\"\r\n\r\n", $relatedBoundary = Uuid::v6()),
            false
        );
        $this->write($fp, sprintf("--%s\r\n", $relatedBoundary), false);

        $this->write(
            $fp,
            sprintf("Content-Type: multipart/alternative; boundary=\"%s\"\r\n\r\n", $alternativeBoundary = Uuid::v6()),
            false
        );
        $this->write($fp, sprintf("--%s\r\n", $alternativeBoundary), false);
        $this->write($fp, "Content-Type: text/plain;charset=\"utf-8\"\r\n", false);
        $this->write($fp, "Content-Transfer-Encoding: quoted-printable\r\n\r\n", false);
        $this->write($fp, quoted_printable_encode(trim($mail->text())) . "\r\n\r\n", false);
        $this->write($fp, sprintf("--%s\r\n", $alternativeBoundary), false);
        $this->write($fp, "Content-Type: text/html;charset=\"utf-8\"\r\n", false);
        $this->write($fp, "Content-Transfer-Encoding: quoted-printable\r\n\r\n", false);
        $this->write($fp, quoted_printable_encode(trim($mail->html())) . "\r\n\r\n", false);
        $this->write($fp, sprintf("--%s--\r\n\r\n", $alternativeBoundary), false);

        //$this->write($fp, sprintf("--%s\r\n", $relatedBoundary), false);
        //$this->write($fp, "Content-Type: image/jpeg;name=\"masthead.png\"\r\n", false);
        //$this->write($fp, "Content-Transfer-Encoding: base64\r\n", false);
        //$this->write($fp, "Content-Disposition: inline;filename=\"masthead.png\"\r\n", false);
        //$this->write($fp, "Content-ID: <masthead.png@".idn_to_ascii($this->config['local_domain']).">\r\n\r\n", false); // src=\"cid:masthead.png@".idn_to_ascii($this->config['local_domain'])."\"
        //$this->write($fp, base64_encode(file_get_contents('/dev/null')."\r\n".), false);
        // html images

        $this->write($fp, sprintf("--%s--\r\n\r\n", $relatedBoundary), false);

        foreach ($mail->attachments() as $attachment) {
            if (\array_key_exists('content', $attachment) || (\array_key_exists('path', $attachment) && file_exists(
                        $attachment['path']
                    ))) {
                $this->write($fp, sprintf("--%s\r\n", $mixedBoundary), false);
                $this->write(
                    $fp,
                    sprintf(
                        "Content-Type: %s; name=\"%s\"\r\n",
                        $attachment['mimetype'] ?? 'application/octet-stream',
                        $attachment['filename']
                    ),
                    false
                );
                $this->write($fp, "Content-Transfer-Encoding: base64\r\n", false);
                $this->write(
                    $fp,
                    sprintf(
                        "Content-Disposition: attachment; filename=\"%s\"\r\n\r\n",
                        mb_encode_mimeheader($attachment['filename'], 'UTF-8', 'Q')
                    ),
                    false
                );
                if (!array_key_exists('content', $attachment)) {
                    $attachment['content'] = file_get_contents($attachment['path']);
                }
                $this->write(
                    $fp,
                    trim(wordwrap(base64_encode($attachment['content']), 76, "\r\n", true)) . "\r\n\r\n",
                    false
                );
            }
        }

        $this->write($fp, sprintf("--%s--\r\n\r\n", $mixedBoundary), false);

        $this->write($fp, ".\r\n",);
        $this->write($fp, "QUIT\r\n", false);
        fclose($fp);

        return true;
    }

    private function write(mixed $fp, string $message, bool $read = true): string
    {
        if (!is_resource($fp)) {
            throw new InvalidArgumentException('Argument #1 of write must be an resource.');
        }
        if (feof($fp)) {
            throw new InvalidArgumentException('Connection closed abnormally.');
        }
        fwrite($fp, $message);
        if ($read === false) {
            return '';
        }
        $response = '';
        do {
            $line = $this->read($fp);
            if ($line === false) {
                throw new RuntimeException('Could not read from mail socket.');
            }
            if (preg_match('/^(\d{3})\s.*/', $line) && !preg_match('/^([23]\d{2})\s.*/', $line)) {
                throw new RuntimeException(trim($line), (int)explode(' ', $line, 2)[0]);
            }
            $response .= $line;
        } while (!preg_match('/^([23]\d{2})\s.*/', $line));
        return $response;
    }

    private function read(mixed $fp): string|false
    {
        if (!is_resource($fp)) {
            throw new InvalidArgumentException('Argument #1 of read must be an resource.');
        }
        if (feof($fp)) {
            throw new InvalidArgumentException('Connection closed abnormally.');
        }
        $result = fread($fp, 8192);
        if ($result === false) {
            return false;
        }
        return $result;
    }
}
