<!doctype html>
<html lang="en">
<head>
    <title>403 Forbidden</title>
    <meta name="robots" content="noindex">
</head>
<body>
<h1>403 Forbidden</h1>
<p>
    Your request was blocked!<br>
    Your IP Address
    <a href="https://www.projecthoneypot.org/ip_{{ $ip }}" target="_blank" rel="noopener">{{ $ip }}</a>
    was blacklisted by the
    <a href="https://www.projecthoneypot.org" target="_blank" rel="noopener">projecthoneypot.org</a>.
</p>
<p>Response-ID: {{ $response->id() }}</p>
</body>
</html>
