<?php

declare(strict_types=1);

namespace TinyFramework\Mail;

use InvalidArgumentException;

abstract class MailerAwesome implements MailerInterface
{
    protected function encodeAddress(string $address): string
    {
        if ($i = strrpos($address, '@')) {
            $local = substr($address, 0, $i);
            $domain = substr($address, $i + 1);
            $address = sprintf(
                '%s@%s',
                $local,
                function_exists('idn_to_ascii') ? idn_to_ascii($domain) : $domain
            );
        } else {
            $local = $address;
        }
        if (preg_match('/[^\x00-\x7F]/', $local)) {
            throw new InvalidArgumentException('Non-ASCII characters not supported in local-part.');
        }
        return $address;
    }

    protected function compileAddressHeader(array $items): string
    {
        return trim(array_reduce($items, function ($result, $item) {
            $line = $this->encodeAddress($item['email']);
            if ($item['name'] && $item['name'] !== $item['email']) {
                $line = sprintf('%s <%s>', mb_encode_mimeheader($item['name'], 'UTF-8', 'Q'), $line);
            }
            return $result . ', ' . $line;
        }, ''), ',');
    }

    abstract public function send(Mail $mail): bool;
}
