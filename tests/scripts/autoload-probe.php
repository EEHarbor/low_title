<?php

error_reporting(E_ALL);
$root = dirname(__DIR__, 2);
require dirname(__DIR__) . '/Support/Coverage.php';
\LowTitleTests\Coverage::start($root);
require dirname(__DIR__) . '/Support/Environment.php';
set_error_handler(array(\LowTitleTests\Environment::class, 'diagnostic'), E_ALL);
$case = $argv[1];
define('PATH_THIRD', $case === 'missing-archive' ? $argv[2] . '/absent/' : $root . '/');
try {
    require $root . '/low_title/autoload.php';
    $autoloaders = spl_autoload_functions();
    $loader = end($autoloaders);
    if ($case === 'unrelated') {
        if ($loader('DifferentVendor\\Example') !== null) {
            throw new RuntimeException('Unrelated namespaces must not be handled');
        }
    }
    if ($case === 'sibling-namespace') {
        if ($loader('Low\\Title\\FluxCapacitorExtra\\Example') !== null) {
            throw new RuntimeException('Sibling namespaces must not be handled');
        }
    }
    if ($case === 'missing-archive' || $case === 'missing-class') {
        try {
            $loader('Low\\Title\\FluxCapacitor\\NotShipped');
            throw new RuntimeException('Missing source must throw a descriptive exception');
        } catch (Exception $exception) {
            $needle = $case === 'missing-archive' ? 'Add-on PHAR file at location ' : 'Class at location ';
            if (strpos($exception->getMessage(), $needle) !== 0) {
                throw $exception;
            }
        }
    }
    if ($case === 'no-opcache-api' && function_exists('opcache_get_configuration')) {
        throw new RuntimeException('The cache API was not disabled for its capability control');
    }
    if ($case === 'restricted-opcache-api' && function_exists('opcache_get_configuration') && ini_get('opcache.restrict_api') !== $argv[2]) {
        throw new RuntimeException('The cache API restriction was not installed for its control');
    }
    if (in_array($case, array('native', 'opcache', 'extraction', 'no-opcache-api', 'restricted-opcache-api'), true)) {
        if ($case === 'extraction') {
            stream_wrapper_unregister('phar');
        }
        if (!class_exists('Low\\Title\\FluxCapacitor\\Base\\Pi')) {
            throw new RuntimeException('The shipped Pi class was not loaded');
        }
    }
    $result = array('ok' => true, 'opcache_api' => function_exists('opcache_get_configuration'));
    if ($case === 'extraction') {
        $result['environment_tmpdir'] = getenv('TMPDIR');
        $result['extraction_directory'] = class_exists('Extract_low_title', false) ? Extract_low_title::$temp : null;
    }
    echo json_encode($result);
} catch (Throwable $exception) {
    echo json_encode(array('ok' => false, 'class' => get_class($exception), 'message' => $exception->getMessage(), 'file' => $exception->getFile(), 'line' => $exception->getLine(), 'opcache_api' => function_exists('opcache_get_configuration'), 'environment_tmpdir' => getenv('TMPDIR'), 'extraction_directory' => class_exists('Extract_low_title', false) ? Extract_low_title::$temp : null));
    exit(1);
}
