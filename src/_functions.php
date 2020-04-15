<?php

/**
 * Returns configuration value from global configuration.
 */
function config($name) {
    static $config = null;

    if (!isset($config)) {
        $defaultConfig = require __DIR__ . '/../config/default.php';

        if (php_sapi_name() == 'cli') {
            $host = 'localhost';
        } else {
            $host = $_SERVER['HTTP_HOST'];
        }
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

function locale_to_flag($locale) {
    static $languageToCountry = [
        'en' => 'GB',
        'de' => 'DE',
        'cs' => 'CZ',
    ];

    $structLocale = explode('-', $locale);

    if (isset($structLocale[1])) {
        $countryCode = strtoupper($structLocale[1]);
    } else {
        $countryCode = $languageToCountry[$structLocale[0]] ?? null;
        if (!$countryCode) {
            return null;
        }
    }

    // convert country code to flag
    static $flagOffset = 0x1F1E6;
    static $asciiOffset = 0x41;

    $firstChar = ord($countryCode[0]) - $asciiOffset + $flagOffset;
    $secondChar = ord($countryCode[1]) - $asciiOffset + $flagOffset;

    return mb_chr($firstChar, 'utf-8') . mb_chr($secondChar, 'utf-8');
}

function group_by($collection, $column) {
    $out = [];

    foreach ($collection as $item) {
        $key = $item[$column];
        if (isset($out[$key])) {
            $out[$key][] = $item;
        } else {
            $out[$key] = [$item];
        }
    }

    return $out;
}
