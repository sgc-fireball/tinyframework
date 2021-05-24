<?php declare(strict_types=1);

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

    public function __construct(string $url = null)
    {
        if (!empty($url)) {
            $parts = parse_url($url);
            $this->schema = $parts['scheme'] ?? null;
            $this->user = $parts['user'] ?? null;
            $this->pass = $parts['pass'] ?? null;
            $this->host = array_key_exists('host', $parts) && $parts['host'] ? ltrim($parts['host'], ':') : null;
            $this->port = $parts['port'] ?? null;
            $this->path = $parts['path'] ?? '/';
            $this->query = $parts['query'] ?? null;
            $this->fragment = $parts['fragment'] ?? null;
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
        if (is_null($scheme)) {
            return $this->schema;
        }
        if (!preg_match('/^[a-z]([a-z0-9+.-]+)$/', $scheme)) {
            throw new InvalidArgumentException('Invalid schema.');
        }
        $url = $this->clone();
        $url->schema = $scheme;
        return $url;
    }

    public function userInfo(string $user = null, string $pass = null): URL|string|null
    {
        if (is_null($user)) {
            return ($this->user . ($this->pass ? ':' . $this->pass : '')) ?: null;
        }
        $url = $this->clone();
        $url->user = $user;
        $url->pass = $pass;
        return $url;
    }

    public function authority(): string|null
    {
        return (($this->user ? $this->user . '@' : '') . $this->host . ($this->port ? ':' . $this->port : '')) ?: null;
    }

    public function host(string $host = null): URL|string|null
    {
        if (is_null($host)) {
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
        if (is_null($port)) {
            return $this->port;
        }
        if ($port < 0 || $port > 65535) {
            throw new InvalidArgumentException('Invalid port.');
        }
        $url = $this->clone();
        $url->port = (int)$port;
        return $url;
    }

    public function path(string $path = null): URL|string
    {
        if (is_null($path)) {
            return $this->path ?: '/';
        }
        $url = $this->clone();
        $url->path = $path;
        return $url;
    }

    public function query(string|array $query = null): URL|string
    {
        if (is_null($query)) {
            return $this->query;
        }
        $url = $this->clone();
        $url->query = is_array($query) ? http_build_query($query) : $query;
        return $url;
    }

    public function fragment(string $fragment = null): URL|string|null
    {
        if (is_null($fragment)) {
            return $this->fragment;
        }
        $url = $this->clone();
        $url->fragment = $fragment;
        return $url;
    }

    private function needPort(): bool
    {
        if ($this->schema === 'tcpmux' && $this->port === 1) return false;
        if ($this->schema === 'echo' && $this->port === 7) return false;
        if ($this->schema === 'discard' && $this->port === 9) return false;
        if ($this->schema === 'systat' && $this->port === 11) return false;
        if ($this->schema === 'daytime' && $this->port === 13) return false;
        if ($this->schema === 'netstat' && $this->port === 15) return false;
        if ($this->schema === 'qotd' && $this->port === 17) return false;
        if ($this->schema === 'chargen' && $this->port === 19) return false;
        if ($this->schema === 'ftp-data' && $this->port === 20) return false;
        if ($this->schema === 'ftp' && $this->port === 21) return false;
        if ($this->schema === 'ssh' && $this->port === 22) return false;
        if ($this->schema === 'telnet' && $this->port === 23) return false;
        if ($this->schema === 'smtp' && $this->port === 25) return false;
        if ($this->schema === 'time' && $this->port === 37) return false;
        if ($this->schema === 'whois' && $this->port === 43) return false;
        if ($this->schema === 'tacacs' && $this->port === 49) return false;
        if ($this->schema === 'domain' && $this->port === 53) return false;
        if ($this->schema === 'gopher' && $this->port === 70) return false;
        if ($this->schema === 'finger' && $this->port === 79) return false;
        if ($this->schema === 'http' && $this->port === 80) return false;
        if ($this->schema === 'kerberos' && $this->port === 88) return false;
        if ($this->schema === 'iso-tsap' && $this->port === 102) return false;
        if ($this->schema === 'acr-nema' && $this->port === 104) return false;
        if ($this->schema === 'pop3' && $this->port === 110) return false;
        if ($this->schema === 'sunrpc' && $this->port === 111) return false;
        if ($this->schema === 'auth' && $this->port === 113) return false;
        if ($this->schema === 'nntp' && $this->port === 119) return false;
        if ($this->schema === 'epmap' && $this->port === 135) return false;
        if ($this->schema === 'netbios-ns' && $this->port === 137) return false;
        if ($this->schema === 'netbios-dgm' && $this->port === 138) return false;
        if ($this->schema === 'netbios-ssn' && $this->port === 139) return false;
        if ($this->schema === 'imap2' && $this->port === 143) return false;
        if ($this->schema === 'snmp' && $this->port === 161) return false;
        if ($this->schema === 'snmp-trap' && $this->port === 162) return false;
        if ($this->schema === 'cmip-man' && $this->port === 163) return false;
        if ($this->schema === 'cmip-agent' && $this->port === 164) return false;
        if ($this->schema === 'mailq' && $this->port === 174) return false;
        if ($this->schema === 'bgp' && $this->port === 179) return false;
        if ($this->schema === 'smux' && $this->port === 199) return false;
        if ($this->schema === 'qmtp' && $this->port === 209) return false;
        if ($this->schema === 'z3950' && $this->port === 210) return false;
        if ($this->schema === 'pawserv' && $this->port === 345) return false;
        if ($this->schema === 'zserv' && $this->port === 346) return false;
        if ($this->schema === 'rpc2portmap' && $this->port === 369) return false;
        if ($this->schema === 'codaauth2' && $this->port === 370) return false;
        if ($this->schema === 'ldap' && $this->port === 389) return false;
        if ($this->schema === 'svrloc' && $this->port === 427) return false;
        if ($this->schema === 'https' && $this->port === 443) return false;
        if ($this->schema === 'snpp' && $this->port === 444) return false;
        if ($this->schema === 'microsoft-ds' && $this->port === 445) return false;
        if ($this->schema === 'kpasswd' && $this->port === 464) return false;
        if ($this->schema === 'submissions' && $this->port === 465) return false;
        if ($this->schema === 'saft' && $this->port === 487) return false;
        if ($this->schema === 'rtsp' && $this->port === 554) return false;
        if ($this->schema === 'nqs' && $this->port === 607) return false;
        if ($this->schema === 'qmqp' && $this->port === 628) return false;
        if ($this->schema === 'ipp' && $this->port === 631) return false;
        if ($this->schema === 'exec' && $this->port === 512) return false;
        if ($this->schema === 'login' && $this->port === 513) return false;
        if ($this->schema === 'shell' && $this->port === 514) return false;
        if ($this->schema === 'printer' && $this->port === 515) return false;
        if ($this->schema === 'gdomap' && $this->port === 538) return false;
        if ($this->schema === 'uucp' && $this->port === 540) return false;
        if ($this->schema === 'klogin' && $this->port === 543) return false;
        if ($this->schema === 'kshell' && $this->port === 544) return false;
        if ($this->schema === 'afpovertcp' && $this->port === 548) return false;
        if ($this->schema === 'nntps' && $this->port === 563) return false;
        if ($this->schema === 'submission' && $this->port === 587) return false;
        if ($this->schema === 'ldaps' && $this->port === 636) return false;
        if ($this->schema === 'tinc' && $this->port === 655) return false;
        if ($this->schema === 'silc' && $this->port === 706) return false;
        if ($this->schema === 'kerberos-adm' && $this->port === 749) return false;
        if ($this->schema === 'domain-s' && $this->port === 853) return false;
        if ($this->schema === 'rsync' && $this->port === 873) return false;
        if ($this->schema === 'ftps-data' && $this->port === 989) return false;
        if ($this->schema === 'ftps' && $this->port === 990) return false;
        if ($this->schema === 'telnets' && $this->port === 992) return false;
        if ($this->schema === 'imaps' && $this->port === 993) return false;
        if ($this->schema === 'pop3s' && $this->port === 995) return false;
        if ($this->schema === 'socks' && $this->port === 1080) return false;
        if ($this->schema === 'proofd' && $this->port === 1093) return false;
        if ($this->schema === 'rootd' && $this->port === 1094) return false;
        if ($this->schema === 'openvpn' && $this->port === 1194) return false;
        if ($this->schema === 'rmiregistry' && $this->port === 1099) return false;
        if ($this->schema === 'lotusnote' && $this->port === 1352) return false;
        if ($this->schema === 'ms-sql-s' && $this->port === 1433) return false;
        if ($this->schema === 'ms-sql-m' && $this->port === 1434) return false;
        if ($this->schema === 'ingreslock' && $this->port === 1524) return false;
        if ($this->schema === 'datametrics' && $this->port === 1645) return false;
        if ($this->schema === 'sa-msg-port' && $this->port === 1646) return false;
        if ($this->schema === 'kermit' && $this->port === 1649) return false;
        if ($this->schema === 'groupwise' && $this->port === 1677) return false;
        if ($this->schema === 'radius' && $this->port === 1812) return false;
        if ($this->schema === 'radius-acct' && $this->port === 1813) return false;
        if ($this->schema === 'cisco-sccp' && $this->port === 2000) return false;
        if ($this->schema === 'nfs' && $this->port === 2049) return false;
        if ($this->schema === 'gnunet' && $this->port === 2086) return false;
        if ($this->schema === 'rtcm-sc104' && $this->port === 2101) return false;
        if ($this->schema === 'gsigatekeeper' && $this->port === 2119) return false;
        if ($this->schema === 'gris' && $this->port === 2135) return false;
        if ($this->schema === 'cvspserver' && $this->port === 2401) return false;
        if ($this->schema === 'venus' && $this->port === 2430) return false;
        if ($this->schema === 'venus-se' && $this->port === 2431) return false;
        if ($this->schema === 'codasrv' && $this->port === 2432) return false;
        if ($this->schema === 'codasrv-se' && $this->port === 2433) return false;
        if ($this->schema === 'mon' && $this->port === 2583) return false;
        if ($this->schema === 'dict' && $this->port === 2628) return false;
        if ($this->schema === 'f5-globalsite' && $this->port === 2792) return false;
        if ($this->schema === 'gsiftp' && $this->port === 2811) return false;
        if ($this->schema === 'gpsd' && $this->port === 2947) return false;
        if ($this->schema === 'gds-db' && $this->port === 3050) return false;
        if ($this->schema === 'icpv2' && $this->port === 3130) return false;
        if ($this->schema === 'isns' && $this->port === 3205) return false;
        if ($this->schema === 'iscsi-target' && $this->port === 3260) return false;
        if ($this->schema === 'mysql' && $this->port === 3306) return false;
        if ($this->schema === 'ms-wbt-server' && $this->port === 3389) return false;
        if ($this->schema === 'nut' && $this->port === 3493) return false;
        if ($this->schema === 'distcc' && $this->port === 3632) return false;
        if ($this->schema === 'daap' && $this->port === 3689) return false;
        if ($this->schema === 'svn' && $this->port === 3690) return false;
        if ($this->schema === 'suucp' && $this->port === 4031) return false;
        if ($this->schema === 'sysrqd' && $this->port === 4094) return false;
        if ($this->schema === 'sieve' && $this->port === 4190) return false;
        if ($this->schema === 'epmd' && $this->port === 4369) return false;
        if ($this->schema === 'remctl' && $this->port === 4373) return false;
        if ($this->schema === 'f5-iquery' && $this->port === 4353) return false;
        if ($this->schema === 'iax' && $this->port === 4569) return false;
        if ($this->schema === 'mtn' && $this->port === 4691) return false;
        if ($this->schema === 'radmin-port' && $this->port === 4899) return false;
        if ($this->schema === 'sip' && $this->port === 5060) return false;
        if ($this->schema === 'sip-tls' && $this->port === 5061) return false;
        if ($this->schema === 'xmpp-client' && $this->port === 5222) return false;
        if ($this->schema === 'xmpp-server' && $this->port === 5269) return false;
        if ($this->schema === 'cfengine' && $this->port === 5308) return false;
        if ($this->schema === 'postgresql' && $this->port === 5432) return false;
        if ($this->schema === 'freeciv' && $this->port === 5556) return false;
        if ($this->schema === 'amqps' && $this->port === 5671) return false;
        if ($this->schema === 'amqp' && $this->port === 5672) return false;
        if ($this->schema === 'x11' && $this->port === 6000) return false;
        if ($this->schema === 'x11-1' && $this->port === 6001) return false;
        if ($this->schema === 'x11-2' && $this->port === 6002) return false;
        if ($this->schema === 'x11-3' && $this->port === 6003) return false;
        if ($this->schema === 'x11-4' && $this->port === 6004) return false;
        if ($this->schema === 'x11-5' && $this->port === 6005) return false;
        if ($this->schema === 'x11-6' && $this->port === 6006) return false;
        if ($this->schema === 'x11-7' && $this->port === 6007) return false;
        if ($this->schema === 'gnutella-svc' && $this->port === 6346) return false;
        if ($this->schema === 'gnutella-rtr' && $this->port === 6347) return false;
        if ($this->schema === 'sge-qmaster' && $this->port === 6444) return false;
        if ($this->schema === 'sge-execd' && $this->port === 6445) return false;
        if ($this->schema === 'mysql-proxy' && $this->port === 6446) return false;
        if ($this->schema === 'ircs-u' && $this->port === 6697) return false;
        if ($this->schema === 'afs3-fileserver' && $this->port === 7000) return false;
        if ($this->schema === 'afs3-callback' && $this->port === 7001) return false;
        if ($this->schema === 'afs3-prserver' && $this->port === 7002) return false;
        if ($this->schema === 'afs3-vlserver' && $this->port === 7003) return false;
        if ($this->schema === 'afs3-kaserver' && $this->port === 7004) return false;
        if ($this->schema === 'afs3-volser' && $this->port === 7005) return false;
        if ($this->schema === 'afs3-errors' && $this->port === 7006) return false;
        if ($this->schema === 'afs3-bos' && $this->port === 7007) return false;
        if ($this->schema === 'afs3-update' && $this->port === 7008) return false;
        if ($this->schema === 'afs3-rmtsys' && $this->port === 7009) return false;
        if ($this->schema === 'font-service' && $this->port === 7100) return false;
        if ($this->schema === 'http-alt' && $this->port === 8080) return false;
        if ($this->schema === 'puppet' && $this->port === 8140) return false;
        if ($this->schema === 'bacula-dir' && $this->port === 9101) return false;
        if ($this->schema === 'bacula-fd' && $this->port === 9102) return false;
        if ($this->schema === 'bacula-sd' && $this->port === 9103) return false;
        if ($this->schema === 'xmms2' && $this->port === 9667) return false;
        if ($this->schema === 'nbd' && $this->port === 10809) return false;
        if ($this->schema === 'zabbix-agent' && $this->port === 10050) return false;
        if ($this->schema === 'zabbix-trapper' && $this->port === 10051) return false;
        if ($this->schema === 'amanda' && $this->port === 10080) return false;
        if ($this->schema === 'dicom' && $this->port === 11112) return false;
        if ($this->schema === 'hkp' && $this->port === 11371) return false;
        if ($this->schema === 'db-lsp' && $this->port === 17500) return false;
        if ($this->schema === 'dcap' && $this->port === 22125) return false;
        if ($this->schema === 'gsidcap' && $this->port === 22128) return false;
        if ($this->schema === 'wnn6' && $this->port === 22273) return false;
        if ($this->schema === 'kerberos4' && $this->port === 750) return false;
        if ($this->schema === 'kerberos-master' && $this->port === 751) return false;
        if ($this->schema === 'krb-prop' && $this->port === 754) return false;
        if ($this->schema === 'iprop' && $this->port === 2121) return false;
        if ($this->schema === 'supfilesrv' && $this->port === 871) return false;
        if ($this->schema === 'supfiledbg' && $this->port === 1127) return false;
        if ($this->schema === 'poppassd' && $this->port === 106) return false;
        if ($this->schema === 'moira-db' && $this->port === 775) return false;
        if ($this->schema === 'moira-update' && $this->port === 777) return false;
        if ($this->schema === 'spamd' && $this->port === 783) return false;
        if ($this->schema === 'skkserv' && $this->port === 1178) return false;
        if ($this->schema === 'rmtcfg' && $this->port === 1236) return false;
        if ($this->schema === 'xtel' && $this->port === 1313) return false;
        if ($this->schema === 'xtelw' && $this->port === 1314) return false;
        if ($this->schema === 'support' && $this->port === 1529) return false;
        if ($this->schema === 'cfinger' && $this->port === 2003) return false;
        if ($this->schema === 'frox' && $this->port === 2121) return false;
        if ($this->schema === 'zebrasrv' && $this->port === 2600) return false;
        if ($this->schema === 'zebra' && $this->port === 2601) return false;
        if ($this->schema === 'ripd' && $this->port === 2602) return false;
        if ($this->schema === 'ripngd' && $this->port === 2603) return false;
        if ($this->schema === 'ospfd' && $this->port === 2604) return false;
        if ($this->schema === 'bgpd' && $this->port === 2605) return false;
        if ($this->schema === 'ospf6d' && $this->port === 2606) return false;
        if ($this->schema === 'ospfapi' && $this->port === 2607) return false;
        if ($this->schema === 'isisd' && $this->port === 2608) return false;
        if ($this->schema === 'afbackup' && $this->port === 2988) return false;
        if ($this->schema === 'afmbackup' && $this->port === 2989) return false;
        if ($this->schema === 'fax' && $this->port === 4557) return false;
        if ($this->schema === 'hylafax' && $this->port === 4559) return false;
        if ($this->schema === 'distmp3' && $this->port === 4600) return false;
        if ($this->schema === 'munin' && $this->port === 4949) return false;
        if ($this->schema === 'enbd-cstatd' && $this->port === 5051) return false;
        if ($this->schema === 'enbd-sstatd' && $this->port === 5052) return false;
        if ($this->schema === 'pcrd' && $this->port === 5151) return false;
        if ($this->schema === 'noclog' && $this->port === 5354) return false;
        if ($this->schema === 'hostmon' && $this->port === 5355) return false;
        if ($this->schema === 'nrpe' && $this->port === 5666) return false;
        if ($this->schema === 'nsca' && $this->port === 5667) return false;
        if ($this->schema === 'mrtd' && $this->port === 5674) return false;
        if ($this->schema === 'bgpsim' && $this->port === 5675) return false;
        if ($this->schema === 'canna' && $this->port === 5680) return false;
        if ($this->schema === 'syslog-tls' && $this->port === 6514) return false;
        if ($this->schema === 'sane-port' && $this->port === 6566) return false;
        if ($this->schema === 'ircd' && $this->port === 6667) return false;
        if ($this->schema === 'zope-ftp' && $this->port === 8021) return false;
        if ($this->schema === 'tproxy' && $this->port === 8081) return false;
        if ($this->schema === 'omniorb' && $this->port === 8088) return false;
        if ($this->schema === 'clc-build-daemon' && $this->port === 8990) return false;
        if ($this->schema === 'xinetd' && $this->port === 9098) return false;
        if ($this->schema === 'git' && $this->port === 9418) return false;
        if ($this->schema === 'zope' && $this->port === 9673) return false;
        if ($this->schema === 'webmin' && $this->port === 10000) return false;
        if ($this->schema === 'kamanda' && $this->port === 10081) return false;
        if ($this->schema === 'amandaidx' && $this->port === 10082) return false;
        if ($this->schema === 'amidxtape' && $this->port === 10083) return false;
        if ($this->schema === 'smsqp' && $this->port === 11201) return false;
        if ($this->schema === 'xpilot' && $this->port === 15345) return false;
        if ($this->schema === 'sgi-cad' && $this->port === 17004) return false;
        if ($this->schema === 'isdnlog' && $this->port === 20011) return false;
        if ($this->schema === 'vboxd' && $this->port === 20012) return false;
        if ($this->schema === 'binkp' && $this->port === 24554) return false;
        if ($this->schema === 'asp' && $this->port === 27374) return false;
        if ($this->schema === 'csync2' && $this->port === 30865) return false;
        if ($this->schema === 'dircproxy' && $this->port === 57000) return false;
        if ($this->schema === 'tfido' && $this->port === 60177) return false;
        if ($this->schema === 'fido' && $this->port === 60179) return false;

        return true;
    }

    /**
     * @return string
     */
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
        return $url;
    }

}
