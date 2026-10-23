<?php

error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);
$case = $argv[1];
$directory = $argv[2];
$root = dirname(__DIR__, 2);
require dirname(__DIR__) . '/Support/Coverage.php';
\LowTitleTests\Coverage::start($root);
require $root . '/tests/Support/Environment.php';
set_error_handler(array(\LowTitleTests\Environment::class, 'diagnostic'), E_ALL);
$GLOBALS['low_title_ee'] = new \LowTitleTests\Environment;
$GLOBALS['low_title_instance_calls'] = 0;

/**
 * Supply the legacy EE singleton without predeclaring the shipped ee() fallback.
 *
 * @return LowTitleTests\Environment
 */
function get_instance()
{
    $GLOBALS['low_title_instance_calls']++;

    return $GLOBALS['low_title_ee'];
}

/**
 * Remove only fixture-owned files after either successful or failed loading.
 *
 * @param string $directory
 * @return void
 */
function removeLoadingFixtures($directory)
{
    foreach (new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS) as $member) {
        if ($member->isDir() && !$member->isLink()) {
            removeLoadingFixtures($member->getPathname());
            rmdir($member->getPathname());
            continue;
        }
        unlink($member->getPathname());
    }
}

$result = array('ok' => false, 'case' => $case, 'fixture_root' => $directory);
$status = 0;
try {
    define('BASEPATH', $directory . '/');
    define('PATH_THIRD', $root . '/');
    define('PATH_CACHE', $directory . '/cache/');
    define('REQ', 'PAGE');
    define('BASE', '/admin.php?S=0');
    define('AMP', '&amp;');
    define('SYSPATH', $directory . '/system/');
    $application = $directory . '/ordinary-parent/expressionengine/';
    if ($case === 'fieldtype-installer') {
        $application = $directory . '/ordinary-parent/installer/';
    }
    if ($case === 'fieldtype-installer-ancestor') {
        $application = $directory . '/installer-parent/installer/';
    }
    define('APPPATH', $application);
    if (strpos($case, 'legacy-') !== 0) {
        define('APP_VER', '7.5.0');
    }
    if ($case === 'legacy-defined') {
        define('APP_VER', '2.5.0');
    }
    $GLOBALS['low_title_ee']->config->config['app_version'] = $case === 'legacy-defined' ? '2.6.0' : '2.5.0';
    if ($case === 'fieldtype-defined-version') {
        define('Low\Title\VERSION', 'fixture-version');
    }
    $expected = null;
    if (strpos($case, 'fieldtype-') === 0) {
        $system = SYSPATH . 'ee/legacy/fieldtypes/EE_Fieldtype.php';
        $app = APPPATH . 'fieldtypes/EE_Fieldtype.php';
        if ($case === 'fieldtype-system' || $case === 'fieldtype-priority') {
            mkdir(dirname($system), 0777, true);
            copy($root . '/tests/Support/Fieldtype.php', $system);
            $expected = $system;
        }
        if (in_array($case, array('fieldtype-app', 'fieldtype-priority', 'fieldtype-defined-version'), true)) {
            mkdir(dirname($app), 0777, true);
            copy($root . '/tests/Support/Fieldtype.php', $app);
            if ($expected === null) {
                $expected = $app;
            }
        }
        if ($case === 'fieldtype-installer' || $case === 'fieldtype-installer-ancestor') {
            $parent = $case === 'fieldtype-installer' ? '/ordinary-parent/' : '/installer-parent/';
            $expected = $directory . $parent . 'expressionengine/fieldtypes/EE_Fieldtype.php';
            mkdir(dirname($expected), 0777, true);
            copy($root . '/tests/Support/Fieldtype.php', $expected);
        }
        if ($case === 'fieldtype-predeclared') {
            $expected = $root . '/tests/Support/Fieldtype.php';
            require $expected;
        }
    }
    require $root . '/low_title/autoload.php';
    if (strpos($case, 'legacy-') === 0) {
        $bridge = new \Low\Title\FluxCapacitor\Bridge\EE(array('module' => 'low_title', 'module_name' => 'Low Title'));
        $version = '2.5.0';
        $parent = 'EE2';
        if (APP_VER !== $version || $bridge->getEEVersion(false) !== $version || (new ReflectionClass($bridge))->getParentClass()->getShortName() !== $parent) {
            throw new RuntimeException('The shipped selector did not preserve native/configured EE version selection');
        }
        $before = $GLOBALS['low_title_instance_calls'];
        if (ee() !== $GLOBALS['low_title_ee'] || $GLOBALS['low_title_instance_calls'] !== $before + 1) {
            throw new RuntimeException('The shipped ee() did not delegate to the legacy singleton');
        }
        $function = new ReflectionFunction('ee');
        if (strpos($function->getFileName(), '.phar/FluxCapacitor/Bridge/EE.php') === false) {
            throw new RuntimeException('The fallback function was not loaded from the shipped archive');
        }
        $result['ee_version'] = APP_VER;
        $result['parent'] = $parent;
    }
    if (strpos($case, 'fieldtype-') === 0) {
        if (!class_exists('Low\\Title\\FluxCapacitor\\Base\\Ft')) {
            throw new RuntimeException('The shipped fieldtype base was not declared');
        }
        $loaded = (new ReflectionClass('EE_Fieldtype'))->getFileName();
        $properties = (new ReflectionClass('Low\\Title\\FluxCapacitor\\Base\\Ft'))->getDefaultProperties();
        $version = $case === 'fieldtype-defined-version' ? 'fixture-version' : json_decode(file_get_contents($root . '/low_title/addon.json'))->version;
        if ($loaded !== realpath($expected) || $properties['info'] !== array('name' => 'Low Title', 'version' => $version)) {
            throw new RuntimeException('The shipped fieldtype loader selected the wrong parent path or version metadata');
        }
        $result['loaded_from'] = $loaded;
        $result['version'] = $version;
    }
    $result['ok'] = true;
} catch (Throwable $exception) {
    $result += array('class' => get_class($exception), 'message' => $exception->getMessage(), 'file' => $exception->getFile(), 'line' => $exception->getLine());
    $status = 1;
} finally {
    removeLoadingFixtures($directory);
    $result['fixtures_remaining'] = iterator_count(new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS));
}
echo json_encode($result);
exit($status);
