<?php

require_once __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', config('errors'));

$db = new Db(config('dbhost'), config('dbuser'), config('dbpass'), config('dbname'));

$rows = $db->query('
    SELECT
        time,
        INET_NTOA(conv(substr(hex(log.ip), -8), 16, 10)) AS ip,
        log.uid,
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
')->fetch_all(MYSQLI_ASSOC);

usort($rows, function ($a, $b) {
    return strcmp($b['time'], $a['time']);
});

$out = array_map(function ($row) {
    $agent = parse_user_agent($row['agent']);
    $row['browser'] = $agent['browser'] . ' ' . $agent['version'];
    $row['platform'] = $agent['platform'];

    $row['bot'] = preg_match('/bot|crawl|slurp|spider|mediapartners/i', $row['agent']) ? "\u{1F916}" : '';

    unset($row['agent']);
    return $row;
}, $rows);

$latte = new Latte\Engine;
$latte->render('templates/view.latte', ['rows' => $out]);
