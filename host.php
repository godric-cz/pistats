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
    ORDER BY 3 DESC
    LIMIT 10
', $hostId);

$visits = $db->query('
    SELECT
        uid,
        date(time) as "date",
        MIN(time) as "start",
        COUNT(1) as "actions",
        COUNT(DISTINCT path) as "paths",
        referrer_ext.value as "referrer_ext"
    FROM log
    JOIN path ON path.id = log.path
    JOIN agent ON agent.id = log.agent AND agent.value NOT REGEXP "dataprovider|bot"
    LEFT JOIN referrer_ext ON referrer_ext.id = referrer_ext
    WHERE host = ?
    GROUP BY 1, 2
    ORDER BY 3 DESC
    LIMIT 20
', $hostId);

$latte = new Latte\Engine;
$latte->render('templates/host.latte', [
    'paths'  => $paths,
    'visits' => $visits,
    'host'   => $host,
]);
