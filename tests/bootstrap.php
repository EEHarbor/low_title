<?php

error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);
require_once __DIR__ . '/Support/Environment.php';
require_once __DIR__ . '/Support/functions.php';

if (!defined('BASEPATH')) {
    define('BASEPATH', dirname(__DIR__) . '/tests/');
    define('PATH_THIRD', dirname(__DIR__) . '/');
    define('PATH_CACHE', sys_get_temp_dir() . '/low-title-tests-' . getmypid() . '/');
    define('FILE_WRITE_MODE', 0666);
    define('DIR_WRITE_MODE', 0777);
    define('APP_VER', getenv('LOW_TITLE_EE_VERSION') ?: '7.5.0');
    define('REQ', getenv('LOW_TITLE_EE_REQUEST') === 'CP' ? 'CP' : 'PAGE');
    define('BASE', '/admin.php?S=0');
    define('AMP', '&amp;');
    define('SYSPATH', __DIR__ . '/fixtures/nonexistent-system/');
    define('APPPATH', __DIR__ . '/fixtures/nonexistent-app/');
}

$GLOBALS['low_title_ee'] = new \LowTitleTests\Environment;
set_error_handler(array(\LowTitleTests\Environment::class, 'diagnostic'), E_ALL);

require_once dirname(__DIR__) . '/low_title/pi.low_title.php';
require_once __DIR__ . '/Support/Plugin.php';
if (class_exists(\PHPUnit\Framework\TestCase::class, false)) {
    require_once __DIR__ . '/tests/Unit/TestCase.php';
}
