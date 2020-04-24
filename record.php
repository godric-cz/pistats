<?php

require_once __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', config('errors'));

header('Access-Control-Allow-Origin: *');

// ip blacklist
if (in_array($_SERVER['REMOTE_ADDR'], config('ignoreIps'))) {
    return;
}

// read data from js
$input = file_get_contents('php://input');
$data = json_decode($input);

if ($data === false) {
    throw new Exception("Data decode failed. Input was '$input'.");
}

// read data from http request
$url = parse_url($_SERVER['HTTP_REFERER']);
$referrer = parse_url($data->referrer);
if (isset($referrer['host']) && $referrer['host'] == $url['host']) {
    $referrerLocal = url_pathquery($referrer);
    $referrerExt = null;
} else {
    $referrerLocal = null;
    $referrerExt = $data->referrer;
}

$uid = (int) ($_COOKIE['pistats'] ?? null);
if (!$uid) {
    $uid = random_int(100000000000000000, 999999999999999999);
    setcookie('pistats', $uid, time() + 3600 * 24 * 365);
}

$acceptLanguage = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
$locale = $acceptLanguage[0] ?? null;

// connect database
$db = new Db(config('dbhost'), config('dbuser'), config('dbpass'), config('dbname'));

// store data
$db->insert('log', [
    'ip'           => $db->ip($_SERVER['REMOTE_ADDR']),
    'uid'          => $uid,
    'host'         => $db->subtable('host', $url['host']),
    'path'         => $db->subtable('path', strip_fbclid(url_pathquery($url))),
    'referrer'     => $db->subtable('path', strip_fbclid($referrerLocal)),
    'referrer_ext' => $db->subtable('referrer_ext', $referrerExt),
    'agent'        => $db->subtable('agent', $_SERVER['HTTP_USER_AGENT']),
    'locale'       => $db->subtable('locale', $locale),
]);
