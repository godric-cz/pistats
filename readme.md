# Pistats

Low-memory in-memory webstats for raspberry pi and shared hosts.

Provést:

```html
<script async src="https://example.com/pistats/js.js"></script>
```

```sql
SELECT
    time,
    INET_NTOA(conv(substr(hex(log.ip), -8), 16, 10)) AS ip,
    host.value AS host,
    path.value AS path,
    referrer.value AS referrer,
    referrer_ext.value AS referrer_ext,
    agent.value AS agent
FROM log
JOIN host ON host.id = log.host
JOIN path ON path.id = log.path
LEFT JOIN path AS referrer ON referrer.id = log.referrer
LEFT JOIN referrer_ext ON referrer_ext.id = log.referrer_ext
JOIN agent ON agent.id = log.agent
```

TODO

- uklidit JS nebo udělat nějakou lokální testovací verzi
- timeout na js soubor pro apache? (možná s if mod enabled)
