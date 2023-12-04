<?php

declare(strict_types=1);

namespace TinyFramework\Http;

use InvalidArgumentException;

class URL implements \Stringable
{
    private ?string $schema = null;

    private ?string $user = null;

    private ?string $pass = null;

    private ?string $host = null;

    private ?int $port = null;

    private string $path = '/';

    private ?string $query = null;

    private ?string $fragment = null;

    public static function factory(string $url = null): URL
    {
        return new URL($url);
    }

    public function __construct(string $url = null)
    {
        if (!empty($url)) {
            $parts = parse_url($url);
            $this->schema = $parts['scheme'] ?? null;
            $this->user = $parts['user'] ?? null;
            $this->pass = $parts['pass'] ?? null;
            $this->host = \array_key_exists('host', $parts) && $parts['host'] ? ltrim($parts['host'], ':') : null;
            $this->port = $parts['port'] ?? null;
            $this->path = $parts['path'] ?? '/';
            $this->query = $parts['query'] ?? null;
            $this->fragment = $parts['fragment'] ?? null;
            if ($this->host && strpos($this->host, ':') !== false) {
                [$host, $port] = explode(':', $this->host);
                $this->host = $host;
                $this->port = (int)$port;
            }
        }
    }

    private function clone(): URL
    {
        $url = new self();
        $url->schema = $this->schema;
        $url->user = $this->user;
        $url->pass = $this->pass;
        $url->host = $this->host;
        $url->port = $this->port;
        $url->path = $this->path;
        $url->query = $this->query;
        $url->fragment = $this->fragment;
        return $url;
    }

    public function scheme(string $scheme = null): URL|string|null
    {
        if ($scheme === null) {
            return $this->schema;
        }
        if (!preg_match('/^[a-z]([a-z0-9+.-]+)$/', $scheme)) {
            throw new InvalidArgumentException('Invalid schema.');
        }
        $url = $this->clone();
        $url->schema = $scheme;
        return $url;
    }

    public function userInfo(string $user = null, #[\SensitiveParameter] string $pass = null): URL|string|null
    {
        if ($user === null) {
            return ($this->user . ($this->pass ? ':' . $this->pass : '')) ?: null;
        }
        $url = $this->clone();
        $url->user = $user;
        $url->pass = $pass;
        return $url;
    }

    public function authority(): string|null
    {
        if ($this->host) {
            $user = $this->user ? $this->user . '@' : '';
            $port = $this->port ? ':' . $this->port : '';
            return $user . $this->host . $port;
        }
        return null;
    }

    public function host(string $host = null): URL|string|null
    {
        if ($host === null) {
            return $this->host;
        }
        if (!filter_var($host, FILTER_VALIDATE_IP) && !filter_var('info@' . $host, FILTER_VALIDATE_DOMAIN)) {
            throw new InvalidArgumentException('Invalid host.');
        }
        $url = $this->clone();
        $url->host = $host;
        return $url;
    }

    public function port(int $port = null): URL|int|null
    {
        if ($port === null) {
            return $this->port;
        }
        if ($port < 0 || $port > 65535) {
            throw new InvalidArgumentException('Invalid port.');
        }
        $url = $this->clone();
        $url->port = $port;
        return $url;
    }

    public function path(string $path = null): URL|string
    {
        if ($path === null) {
            return $this->path ?: '/';
        }
        $url = $this->clone();
        $url->path = $path;
        return $url;
    }

    public function query(string|array $query = null): URL|string
    {
        if ($query === null) {
            return $this->query;
        }
        $url = $this->clone();
        $url->query = \is_array($query) ? http_build_query($query) : $query;
        return $url;
    }

    public function fragment(string $fragment = null): URL|string|null
    {
        if ($fragment === null) {
            return $this->fragment;
        }
        $url = $this->clone();
        $url->fragment = $fragment;
        return $url;
    }

    private function needPort(): bool
    {
        $schemaAndPort = [
            'tcpmux' => 1,
            'echo' => 7,
            'discard' => 9,
            'systat' => 11,
            'daytime' => 13,
            'netstat' => 15,
            'qotd' => 17,
            'chargen' => 19,
            'ftp-data' => 20,
            'ftp' => 21,
            'ssh' => 22,
            'telnet' => 23,
            'smtp' => 25,
            'time' => 37,
            'whois' => 43,
            'tacacs' => 49,
            'domain' => 53,
            'gopher' => 70,
            'finger' => 79,
            'http' => 80,
            'kerberos' => 88,
            'iso-tsap' => 102,
            'acr-nema' => 104,
            'pop3' => 110,
            'sunrpc' => 111,
            'auth' => 113,
            'nntp' => 119,
            'epmap' => 135,
            'netbios-ns' => 137,
            'netbios-dgm' => 138,
            'netbios-ssn' => 139,
            'imap2' => 143,
            'snmp' => 161,
            'snmp-trap' => 162,
            'cmip-man' => 163,
            'cmip-agent' => 164,
            'mailq' => 174,
            'bgp' => 179,
            'smux' => 199,
            'qmtp' => 209,
            'z3950' => 210,
            'pawserv' => 345,
            'zserv' => 346,
            'rpc2portmap' => 369,
            'codaauth2' => 370,
            'ldap' => 389,
            'svrloc' => 427,
            'https' => 443,
            'snpp' => 444,
            'microsoft-ds' => 445,
            'kpasswd' => 464,
            'submissions' => 465,
            'saft' => 487,
            'rtsp' => 554,
            'nqs' => 607,
            'qmqp' => 628,
            'ipp' => 631,
            'exec' => 512,
            'login' => 513,
            'shell' => 514,
            'printer' => 515,
            'gdomap' => 538,
            'uucp' => 540,
            'klogin' => 543,
            'kshell' => 544,
            'afpovertcp' => 548,
            'nntps' => 563,
            'submission' => 587,
            'ldaps' => 636,
            'tinc' => 655,
            'silc' => 706,
            'kerberos-adm' => 749,
            'domain-s' => 853,
            'rsync' => 873,
            'ftps-data' => 989,
            'ftps' => 990,
            'telnets' => 992,
            'imaps' => 993,
            'pop3s' => 995,
            'socks' => 1080,
            'proofd' => 1093,
            'rootd' => 1094,
            'openvpn' => 1194,
            'rmiregistry' => 1099,
            'lotusnote' => 1352,
            'ms-sql-s' => 1433,
            'ms-sql-m' => 1434,
            'ingreslock' => 1524,
            'datametrics' => 1645,
            'sa-msg-port' => 1646,
            'kermit' => 1649,
            'groupwise' => 1677,
            'radius' => 1812,
            'radius-acct' => 1813,
            'cisco-sccp' => 2000,
            'nfs' => 2049,
            'gnunet' => 2086,
            'rtcm-sc104' => 2101,
            'gsigatekeeper' => 2119,
            'gris' => 2135,
            'cvspserver' => 2401,
            'venus' => 2430,
            'venus-se' => 2431,
            'codasrv' => 2432,
            'codasrv-se' => 2433,
            'mon' => 2583,
            'dict' => 2628,
            'f5-globalsite' => 2792,
            'gsiftp' => 2811,
            'gpsd' => 2947,
            'gds-db' => 3050,
            'icpv2' => 3130,
            'isns' => 3205,
            'iscsi-target' => 3260,
            'mysql' => 3306,
            'ms-wbt-server' => 3389,
            'nut' => 3493,
            'distcc' => 3632,
            'daap' => 3689,
            'svn' => 3690,
            'suucp' => 4031,
            'sysrqd' => 4094,
            'sieve' => 4190,
            'epmd' => 4369,
            'remctl' => 4373,
            'f5-iquery' => 4353,
            'iax' => 4569,
            'mtn' => 4691,
            'radmin-port' => 4899,
            'sip' => 5060,
            'sip-tls' => 5061,
            'xmpp-client' => 5222,
            'xmpp-server' => 5269,
            'cfengine' => 5308,
            'postgresql' => 5432,
            'freeciv' => 5556,
            'amqps' => 5671,
            'amqp' => 5672,
            'x11' => 6000,
            'x11-1' => 6001,
            'x11-2' => 6002,
            'x11-3' => 6003,
            'x11-4' => 6004,
            'x11-5' => 6005,
            'x11-6' => 6006,
            'x11-7' => 6007,
            'gnutella-svc' => 6346,
            'gnutella-rtr' => 6347,
            'sge-qmaster' => 6444,
            'sge-execd' => 6445,
            'mysql-proxy' => 6446,
            'ircs-u' => 6697,
            'afs3-fileserver' => 7000,
            'afs3-callback' => 7001,
            'afs3-prserver' => 7002,
            'afs3-vlserver' => 7003,
            'afs3-kaserver' => 7004,
            'afs3-volser' => 7005,
            'afs3-errors' => 7006,
            'afs3-bos' => 7007,
            'afs3-update' => 7008,
            'afs3-rmtsys' => 7009,
            'font-service' => 7100,
            'http-alt' => 8080,
            'puppet' => 8140,
            'bacula-dir' => 9101,
            'bacula-fd' => 9102,
            'bacula-sd' => 9103,
            'xmms2' => 9667,
            'nbd' => 10809,
            'zabbix-agent' => 10050,
            'zabbix-trapper' => 10051,
            'amanda' => 10080,
            'dicom' => 11112,
            'hkp' => 11371,
            'db-lsp' => 17500,
            'dcap' => 22125,
            'gsidcap' => 22128,
            'wnn6' => 22273,
            'kerberos4' => 750,
            'kerberos-master' => 751,
            'krb-prop' => 754,
            'iprop' => 2121,
            'supfilesrv' => 871,
            'supfiledbg' => 1127,
            'poppassd' => 106,
            'moira-db' => 775,
            'moira-update' => 777,
            'spamd' => 783,
            'skkserv' => 1178,
            'rmtcfg' => 1236,
            'xtel' => 1313,
            'xtelw' => 1314,
            'support' => 1529,
            'cfinger' => 2003,
            'frox' => 2121,
            'zebrasrv' => 2600,
            'zebra' => 2601,
            'ripd' => 2602,
            'ripngd' => 2603,
            'ospfd' => 2604,
            'bgpd' => 2605,
            'ospf6d' => 2606,
            'ospfapi' => 2607,
            'isisd' => 2608,
            'afbackup' => 2988,
            'afmbackup' => 2989,
            'fax' => 4557,
            'hylafax' => 4559,
            'distmp3' => 4600,
            'munin' => 4949,
            'enbd-cstatd' => 5051,
            'enbd-sstatd' => 5052,
            'pcrd' => 5151,
            'noclog' => 5354,
            'hostmon' => 5355,
            'nrpe' => 5666,
            'nsca' => 5667,
            'mrtd' => 5674,
            'bgpsim' => 5675,
            'canna' => 5680,
            'syslog-tls' => 6514,
            'sane-port' => 6566,
            'ircd' => 6667,
            'zope-ftp' => 8021,
            'tproxy' => 8081,
            'omniorb' => 8088,
            'clc-build-daemon' => 8990,
            'xinetd' => 9098,
            'git' => 9418,
            'zope' => 9673,
            'webmin' => 10000,
            'kamanda' => 10081,
            'amandaidx' => 10082,
            'amidxtape' => 10083,
            'smsqp' => 11201,
            'xpilot' => 15345,
            'sgi-cad' => 17004,
            'isdnlog' => 20011,
            'vboxd' => 20012,
            'binkp' => 24554,
            'asp' => 27374,
            'csync2' => 30865,
            'dircproxy' => 57000,
            'tfido' => 60177,
            'fido' => 60179,
        ];
        if (array_key_exists($this->schema, $schemaAndPort) && $this->port === $schemaAndPort[$this->schema]) {
            return false;
        }
        return true;
    }

    public function __toString(): string
    {
        $url = '';
        $url .= $this->schema ? $this->schema . '://' : '';
        if ($this->user) {
            $url .= $this->user;
            $url .= $this->pass ? ':' . $this->pass : '';
            $url .= '@';
        }
        $url .= $this->host;
        if ($this->needPort()) {
            $url .= $this->port ? ':' . $this->port : '';
        }
        $url .= $this->path ?? '/';
        $url .= $this->query ? '?' . $this->query : '';
        $url .= $this->fragment ? '#' . $this->fragment : '';
        return rtrim($url, '?&#');
    }

    public function origin(): string
    {
        $url = '';
        $url .= $this->schema ? $this->schema . '://' : '';
        $url .= $this->host;
        if ($this->needPort()) {
            $url .= $this->port ? ':' . $this->port : '';
        }
        return $url;
    }
}
