<?php

require_once __DIR__ . '/../vendor/autoload.php';

return [
    'remote' => config('ftp'),
    'local'  => realpath(__DIR__ . '/..'),
    'ignore' => '
        /.maintenance
        /_bordel
        /config/localhost.php
        /composer.*
        /readme.md
    ',
    'log' => '/dev/null',
];
