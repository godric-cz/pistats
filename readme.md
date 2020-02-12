
Provést:

    <script async src="https://files.korh.cz/pistats/js.js"></script>

    select
    time,
    INET_NTOA(conv(substr(hex(log.ip), -8), 16, 10)) as ip,
    host.value as host,
    path.value as path,
    referrer.value as referrer,
    referrer_ext.value as referrer_ext
    from log
    join host on host.id = log.host
    join path on path.id = log.path
    left join path as referrer on referrer.id = log.referrer
    left join referrer_ext on referrer_ext.id = log.referrer_ext

TODO

- uklidit JS nebo udělat nějakou lokální testovací verzi
- timeout na js soubor pro apache? (možná s if mod enabled)
