<?php

require_once __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', config('errors'));

$db = new Db(config('dbhost'), config('dbuser'), config('dbpass'), config('dbname'));

$hostId = (int) $_GET['hostId'] ?? null;
if (!$hostId) {
    die('no host id');
}

$host = $db->query('SELECT value FROM host WHERE id = ?', $hostId)->fetch_row()[0];

$paths = $db->query('
    SELECT
        path.value as "path",
        COUNT(1) as "count",
        COUNT(DISTINCT uid) as "unique"
    FROM log
    JOIN path ON path.id = log.path
    JOIN agent ON agent.id = log.agent AND agent.value NOT REGEXP "dataprovider|bot"
    WHERE host = ?
    GROUP BY path
    ORDER BY 3 DESC, 2 DESC
    LIMIT 10
', $hostId);

$visits = $db->query('
    SELECT
        uid,
        date(time) as "date",
        MIN(time) as "start",
        COUNT(1) as "actions",
        COUNT(DISTINCT path) as "paths",
        referrer_ext.value as "referrer_ext",
        SUBSTRING(MIN(CONCAT(time, path.value)), 20) as "landing",
        locale.value as "locale"
    FROM log
    JOIN path ON path.id = log.path
    JOIN agent ON agent.id = log.agent AND agent.value NOT REGEXP "dataprovider|bot"
    LEFT JOIN referrer_ext ON referrer_ext.id = referrer_ext
    LEFT JOIN locale ON log.locale = locale.id
    WHERE host = ?
    GROUP BY 1, 2
    ORDER BY 3 DESC
    LIMIT 20
', $hostId);

$latte = new Latte\Engine;
$latte->addFilter('smarttruncate', function ($s, $length) {
    $strlen = mb_strlen($s);
    if ($strlen <= $length) {
        return $s;
    }

    $rpos = mb_strrpos($s, '/');
    $endlen = $strlen - $rpos;
    if ($endlen < $length) { // TODO off 1?
        return mb_substr($s, 0, $length - $endlen) . '…' . mb_substr($s, $rpos);
    } else {
        return mb_substr($s, 0, $length) . '…';
    }
});
$latte->addFilter("locale_to_flag", "locale_to_flag");
$latte->render('templates/host.latte', [
    'paths'  => $paths,
    'visits' => $visits,
    'host'   => $host,
]);
