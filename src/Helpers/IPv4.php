<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

class IPv4
{

    use Macroable;

    private string $ipAddr = '0.0.0.0';

    private int $cidr = 0;

    public function __construct(string $ipAddr = null, int $cidr = null)
    {
        if ($ipAddr) {
            $this->setIp($ipAddr);
        }
        if ($cidr) {
            $this->setCIDR($cidr);
        }
    }

    public function setIp(string $ipAddr): static
    {
        if (!filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \InvalidArgumentException();
        }
        $this->ipAddr = $ipAddr;
        return $this;
    }

    public function getIp(): string
    {
        return $this->ipAddr;
    }

    public function setLong(int $long): static
    {
        if ($long < 0 || $long > pow(2, 32) - 1) {
            throw new \InvalidArgumentException();
        }
        $this->ipAddr = long2ip($long);
        return $this;
    }

    public function getLong(): int
    {
        return ip2long($this->ipAddr);
    }

    public function getInArpa(): string
    {
        return sprintf(
            '%s.in-addr.arpa',
            implode(
                '.',
                array_reverse(
                    explode(
                        '.',
                        $this->ipAddr
                    )
                )
            )
        );
    }

    public function setCIDR(int $cidr): static
    {
        if ($cidr < 0 || $cidr > 32) {
            throw new \InvalidArgumentException();
        }
        $this->cidr = $cidr;
        return $this;
    }

    public function getCIDR(): int
    {
        return $this->cidr;
    }

    public function setSubnetmask(string $ipAddr): static
    {
        if (!filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \InvalidArgumentException();
        }
        $bin = decbin(ip2long($ipAddr));
        if (!preg_match('/^(1{0,32})(0{0,32})$/', $bin) && strlen($bin) != 32) {
            throw new \InvalidArgumentException();
        }
        $this->setCIDR(strlen(trim($bin, '0')));
        return $this;
    }

    public function getSubnetmask(): string
    {
        return long2ip(((1 << 32) - 1) << (32 - $this->cidr));
    }

    public function getNetmask(): string
    {
        $bin = str_pad(substr(decbin(ip2long($this->ipAddr)), 0, $this->cidr), 32, '0', STR_PAD_RIGHT);
        return long2ip(bindec($bin));
    }

    public function getBroadcast(): string
    {
        $bin = str_pad(substr(decbin(ip2long($this->ipAddr)), 0, $this->cidr), 32, '1', STR_PAD_RIGHT);
        return long2ip(bindec($bin));
    }

    public function getIpv6(): string
    {
        [$block1, $block2, $block3, $block4] = explode('.', $this->ipAddr);
        return strtoupper(
            sprintf(
                '2002:%s:%s:%s:%s::',
                str_pad(dechex((int)$block1), 4, '0', STR_PAD_LEFT),
                str_pad(dechex((int)$block2), 4, '0', STR_PAD_LEFT),
                str_pad(dechex((int)$block3), 4, '0', STR_PAD_LEFT),
                str_pad(dechex((int)$block4), 4, '0', STR_PAD_LEFT)
            )
        );
    }

    public function isIpInSubnet(IPv4|string $ipAddr): bool
    {
        $ipAddr = $ipAddr instanceof self ? $ipAddr->getIp() : $ipAddr;
        if (!filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \InvalidArgumentException();
        }
        $min = ip2long($this->getNetmask());
        $ipAddr = ip2long($ipAddr);
        $max = ip2long($this->getBroadcast());
        return $min <= $ipAddr && $ipAddr <= $max;
    }

    public function __toString(): string
    {
        return sprintf('%s/%d', $this->getIp(), $this->getCIDR());
    }
}
