<?php

error_reporting(E_ALL);
$root = dirname(__DIR__, 2);
require dirname(__DIR__) . '/Support/Environment.php';
require dirname(__DIR__) . '/Support/Collaborators.php';
require dirname(__DIR__) . '/Support/functions.php';
set_error_handler(array(\LowTitleTests\Environment::class, 'diagnostic'), E_ALL);
$case = $argv[1];
$directory = $argv[2];
define('BASEPATH', $directory . '/');
define('PATH_THIRD', $directory . '/');
define('PATH_CACHE', $directory . '/cache/');
define('APP_VER', $case === 'legacy-session' ? '2.6.0' : '2.5.0');
define('BASE', '/admin.php?S=0');
define('AMP', '&amp;');
define('REQ', 'PAGE');
$GLOBALS['low_title_ee'] = new \LowTitleTests\Environment;
require $root . '/low_title/autoload.php';
$bridge = new \Low\Title\FluxCapacitor\Bridge\EE2(array('module' => 'low_title', 'module_name' => 'Low Title'));
try {
    if ($case === 'legacy-inline-view') {
        $template = '<h1>Inline fixture</h1>';
        $result = $bridge->view('unused', array(), true, $template);
        if ($result !== 'rendered:dynamic' || file_get_contents($directory . '/low_title/views/dynamic.php') !== $template) {
            throw new RuntimeException('Inline view was not written and selected');
        }
    }
    if ($case === 'legacy-session') {
        ee()->config->config['admin_session_type'] = 's';
        ee()->session = new \LowTitleTests\Recorder;
        ee()->session->returns['userdata'] = 'SESSION';
        if (strpos($bridge->getBaseURL('settings'), '&amp;S=SESSION') === false) {
            throw new RuntimeException('EE2.6 admin session workaround was not applied');
        }
    }
    if ($case === 'legacy-cache') {
        ee()->session = new \LowTitleTests\Recorder;
        if ($bridge->cache('get', 'missing') !== false || $bridge->cache('set', 'key', 'value') !== 'value' || $bridge->cache('get', 'key') !== 'value') {
            throw new RuntimeException('Legacy persistent flag did not fall back to request cache');
        }
    }
    echo json_encode(array('ok' => true, 'case' => $case));
} catch (Throwable $exception) {
    echo json_encode(array('ok' => false, 'case' => $case, 'message' => $exception->getMessage(), 'file' => $exception->getFile(), 'line' => $exception->getLine()));
    exit(1);
}
