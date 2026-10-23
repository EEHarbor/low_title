<?php

error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);
$sourceRoot = dirname(__DIR__, 2) . '/low_title/';
xdebug_set_filter(XDEBUG_FILTER_CODE_COVERAGE, XDEBUG_PATH_INCLUDE, array($sourceRoot, 'phar://' . $sourceRoot . 'low_title.phar/'));
