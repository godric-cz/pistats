<?php

require_once __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', config('errors'));
ini_set('html_errors', false);

header('Access-Control-Allow-Origin: *');

// ip blacklist
if (in_array($_SERVER['REMOTE_ADDR'], config('ignoreIps'))) {
    return;
}

// read data from js
$input = file_get_contents('php://input');
$data = json_decode($input);

if (!$data) {
    http_response_code(400);
    echo '{"error":"malformed or empty input"}'."\n";
    return;
}

// read data from http request
if (!isset($_SERVER['HTTP_REFERER'])) {
    http_response_code(400);
    echo '{"error":"referrer not set"}'."\n";
    return;
}

$url = parse_url($_SERVER['HTTP_REFERER']); // browsers do not send path anymore
$path = strip_fbclid($data->path);
$referrer = parse_url($data->referrer);
if (isset($referrer['host']) && $referrer['host'] == $url['host']) {
    $referrerLocal = url_pathquery($referrer);
    $referrerLocal = strip_fbclid($referrerLocal);
    $referrerExt = null;
} else {
    $referrerLocal = null;
    $referrerExt = $data->referrer;
}

$uid = (int) ($data->uid ?? 0);

$acceptLanguage = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
$locale = $acceptLanguage[0] ?? null;

// connect database
$db = new Db(config('dbhost'), config('dbuser'), config('dbpass'), config('dbname'));

// store data
$db->insert('log', [
    'ip'           => $db->ip($_SERVER['REMOTE_ADDR']),
    'uid'          => $uid,
    'host'         => $db->subtable('host', $url['host']),
    'path'         => $db->subtable('path', $path),
    'referrer'     => $db->subtable('path', $referrerLocal),
    'referrer_ext' => $db->subtable('referrer_ext', $referrerExt),
    'agent'        => $db->subtable('agent', $_SERVER['HTTP_USER_AGENT']),
    'locale'       => $db->subtable('locale', $locale),
]);
