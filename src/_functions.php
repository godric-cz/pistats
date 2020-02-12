<?php

/**
 * Returns configuration value from global configuration.
 */
function config($name) {
    static $config = null;

    if (!isset($config)) {
        $defaultConfig = require __DIR__ . '/../config/default.php';

        $host = $_SERVER['HTTP_HOST'];
        if (!preg_match('/^[a-zA-Z0-9\.]+$/', $host)) {
            throw new Exception('Invalid hostname.');
        }

        $serverConfig = require __DIR__ . '/../config/' . $host . '.php';

        $config = array_merge($defaultConfig, $serverConfig);

        if (!$config) {
            throw new Exception('Configuration is empty.');
        }
    }

    if (!isset($config[$name])) {
        throw new Exception("Configuration '$name' does not exist.");
    }

    return $config[$name];
}

function url_pathquery($url) {
    $path = $url['path'];
    if (!empty($url['query'])) {
        $path .= '?' . $url['query'];
    }
    return $path;
}
