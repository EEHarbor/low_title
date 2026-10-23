<?php

error_reporting(E_ALL);
$branchReport = getenv('LOW_TITLE_BRANCH_REPORT');
if ($branchReport) {
    if (!function_exists('xdebug_start_code_coverage')) {
        throw new RuntimeException('Xdebug branch instrumentation is required');
    }
    xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE | XDEBUG_CC_BRANCH_CHECK);
    register_shutdown_function(function () use ($branchReport) {
        $data = xdebug_get_code_coverage();
        xdebug_stop_code_coverage();
        $sourceRoot = dirname(__DIR__) . '/low_title/';
        $data = array_filter($data, function ($file) use ($sourceRoot) {
            return strpos($file, $sourceRoot) === 0 || strpos($file, 'phar://' . $sourceRoot . 'low_title.phar/') === 0;
        }, ARRAY_FILTER_USE_KEY);
        $observed = false;
        foreach ($data as $file) {
            foreach (isset($file['functions']) ? $file['functions'] : array() as $function) {
                foreach ($function['branches'] as $branch) {
                    $observed = $observed || $branch['hit'] > 0;
                }
            }
        }
        if (!$observed) {
            throw new RuntimeException('No shipped-source function graph was executed');
        }
        $context = array('ee_version' => defined('APP_VER') ? APP_VER : null, 'request' => defined('REQ') ? REQ : null);
        $bridgeClass = 'Low\\Title\\FluxCapacitor\\Bridge\\EE';
        if (class_exists($bridgeClass, false)) {
            $constructor = new ReflectionMethod($bridgeClass, '__construct');
            $context['bridge_constructor'] = array('start_line' => $constructor->getStartLine(), 'end_line' => $constructor->getEndLine());
        }
        file_put_contents($branchReport, json_encode(array('runtime' => PHP_VERSION, 'xdebug' => phpversion('xdebug'), 'context' => $context, 'files' => $data), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    });
}
require_once __DIR__ . '/Support/Environment.php';
require_once __DIR__ . '/Support/Process.php';
require_once __DIR__ . '/Support/Collaborators.php';
require_once __DIR__ . '/Support/functions.php';
require_once __DIR__ . '/Support/Fieldtype.php';

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

$coverageSource = getenv('LOW_TITLE_COVERAGE_SOURCE');
if ($coverageSource) {
    $sourceArchive = new Phar(dirname(__DIR__) . '/low_title/low_title.phar');
    foreach (new RecursiveIteratorIterator($sourceArchive) as $member) {
        if ($member->getExtension() !== 'php') {
            continue;
        }
        $relative = substr($member->getPathname(), strpos($member->getPathname(), '.phar/') + 6);
        $measurementFile = $coverageSource . '/' . $relative;
        if (!is_file($measurementFile) || hash_file('sha256', $measurementFile) !== hash('sha256', $member->getContent())) {
            throw new RuntimeException('Archive measurement copy differs: ' . $relative);
        }
    }
    spl_autoload_register(function ($class) use ($coverageSource) {
        $prefix = 'Low\\Title\\';
        if (strpos($class, $prefix) !== 0) {
            return;
        }

        $file = $coverageSource . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }, true, true);
}

require_once dirname(__DIR__) . '/low_title/pi.low_title.php';
require_once __DIR__ . '/Support/Plugin.php';
if (class_exists(\PHPUnit\Framework\TestCase::class, false)) {
    require_once __DIR__ . '/tests/Unit/TestCase.php';
}
