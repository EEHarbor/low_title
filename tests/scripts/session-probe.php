<?php

error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);
$root = dirname(__DIR__, 2);
$case = $argv[1];
$directory = $argv[2];
require dirname(__DIR__) . '/Support/Coverage.php';
\LowTitleTests\Coverage::start($root);
require dirname(__DIR__) . '/Support/Environment.php';
set_error_handler(array(\LowTitleTests\Environment::class, 'diagnostic'), E_ALL);
$originalDirectory = getcwd();
$session = $directory . '/session';
mkdir($session);
$value = $session;
if ($case === 'depth') {
    $value = '0;' . $session;
}
if ($case === 'mode') {
    $value = '0;0600;' . $session;
}
if ($case === 'missing') {
    $value = $directory . '/missing-session';
}
if ($case === 'file') {
    $value = $directory . '/session-file';
    file_put_contents($value, 'owned session boundary');
}
if ($case === 'empty') {
    $value = '';
}
$temporary = $directory . '/missing-temp';
if ($case === 'tmp-file') {
    $temporary = $directory . '/temp-file';
    file_put_contents($temporary, 'owned temp boundary');
}
if ($case === 'tmp-readonly') {
    $temporary = $directory . '/readonly-temp';
    mkdir($temporary);
    chmod($temporary, 0500);
    if (is_writable($temporary)) {
        throw new RuntimeException('The host did not enforce the owned read-only directory');
    }
}
session_save_path($value);
putenv('TMPDIR=' . $temporary);
$result = array('returned' => false, 'case' => $case, 'fixture_root' => $directory, 'session_value' => session_save_path(), 'tmpdir' => getenv('TMPDIR'), 'tmp_is_directory' => is_dir($temporary), 'tmp_is_writable' => is_writable($temporary), 'native_session' => null);

/**
 * Remove owned fixture contents without following symbolic links.
 *
 * @param string $directory
 * @return void
 */
function removeSessionFixtures($directory)
{
    chmod($directory, 0700);
    foreach (new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS) as $entry) {
        if ($entry->isDir() && !$entry->isLink()) {
            removeSessionFixtures($entry->getPathname());
            rmdir($entry->getPathname());
            continue;
        }
        unlink($entry->getPathname());
    }
}

ob_start();
register_shutdown_function(function () use (&$result, $directory, $originalDirectory) {
    $result['output'] = ob_get_clean();
    $result['extraction_directory'] = class_exists('Extract_low_title', false) ? Extract_low_title::$temp : null;
    chdir($originalDirectory);
    removeSessionFixtures($directory);
    $result['fixtures_remaining'] = iterator_count(new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS));
    echo json_encode($result);
});
try {
    $result['phase'] = 'native-session-contract';
    if (in_array($case, array('plain', 'depth', 'mode', 'tmp-file', 'tmp-readonly', 'empty'), true)) {
        session_id('low-title-' . getmypid());
        if (!session_start()) {
            throw new RuntimeException('The native session handler rejected a valid path');
        }
        $_SESSION['fixture'] = 'owned';
        if (!session_write_close()) {
            throw new RuntimeException('The native session handler did not write its owned fixture');
        }
        $file = ($case === 'empty' ? sys_get_temp_dir() : $session) . '/sess_' . session_id();
        $result['native_session'] = array('exists' => is_file($file), 'mode' => fileperms($file) & 0777);
    }
    $result['phase'] = 'source-extraction';
    require $root . '/low_title/low_title.phar';
    Extract_low_title::go(true);
    $result['returned'] = true;
} catch (Throwable $exception) {
    $result += array('class' => get_class($exception), 'message' => $exception->getMessage(), 'file' => $exception->getFile(), 'line' => $exception->getLine());
    exit(1);
}
