<?php

require_once __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', config('errors'));

$db = new Db(config('dbhost'), config('dbuser'), config('dbpass'), config('dbname'));

$rows = $db->query('
    SELECT
        host as host_id,
        date(time) as date,
        host.value as host,
        count(distinct uid) as visitors,
        count(1) as actions
    FROM log
    JOIN host ON host.id = log.host
    JOIN agent ON agent.id = log.agent AND agent.value NOT REGEXP "dataprovider|bot"
    GROUP BY 1, 2
    ORDER BY 3, 2
')->fetch_all(MYSQLI_ASSOC);

$period = new DatePeriod(
    (new DateTime)->sub(new DateInterval('P14D')),
    new DateInterval('P1D'),
    new DateTime
);

$out = [];
$groups = group_by($rows, 'host');
foreach ($groups as $host => $items) {
    $dates = group_by($items, 'date');
    $out[$host]['hostId'] = $items[0]['host_id'];

    foreach ($period as $dateObject) {
        $requiredDate = $dateObject->format('Y-m-d');

        $row = $dates[$requiredDate][0] ?? null;
        $out[$host]['visitors'][] = (int) $row['visitors'] ?? 0;
        $out[$host]['apv'][] = $row ? $row['actions'] / $row['visitors'] : 0;
    }
}

$latte = new Latte\Engine;
$latte->render('templates/view.latte', ['groups' => $out]);



/*
usort($rows, function ($a, $b) {
    return strcmp($b['time'], $a['time']);
});

$out = array_map(function ($row) {
    $agent = parse_user_agent($row['agent']);
    $row['browser'] = $agent['browser'] . ' ' . $agent['version'];
    $row['platform'] = $agent['platform'];

    $row['loc'] = locale_to_flag($row['locale']);
    unset($row['locale']);

    $row['bot'] = preg_match('/bot|crawl|slurp|spider|mediapartners/i', $row['agent']) ? "\u{1F916}" : '';

    unset($row['agent']);
    return $row;
}, $rows);

$latte = new Latte\Engine;
$latte->render('templates/view.latte', ['rows' => $out]);
*/
