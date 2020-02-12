<?php

require_once __DIR__ . '/src/_functions.php';

ini_set('display_errors', config('errors'));

require_once __DIR__ . '/src/Db.php'; // TODO composer

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

// open config and database TODO
$db = new Db(config('dbhost'), config('dbuser'), config('dbpass'), config('dbname'));

// store data
$db->insert('log', [
    'ip'           => $db->ip($_SERVER['REMOTE_ADDR']),
    'host'         => $db->subtable('host', $url['host']),
    'path'         => $db->subtable('path', url_pathquery($url)),
    'referrer'     => $db->subtable('path', $referrerLocal),
    'referrer_ext' => $db->subtable('referrer_ext', $referrerExt),
    'agent'        => $db->subtable('agent', $_SERVER['HTTP_USER_AGENT']),
    // TODO locale?
    // TODO should we record query in url?
]);
