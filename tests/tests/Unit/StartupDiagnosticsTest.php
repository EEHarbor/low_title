<?php

namespace LowTitleTests;

class StartupDiagnosticsTest extends TestCase
{
    /**
     * Return each entrypoint and its first dependency or operational boundary.
     *
     * @return array
     */
    public static function entrypoints()
    {
        return array(
            'bootstrap' => array('tests/bootstrap.php', 'require_once'),
            'raw-prepend' => array('tests/Support/BranchFilter.php', '$sourceRoot ='),
            'autoload' => array('tests/scripts/autoload-probe.php', '$root ='),
            'fixture' => array('tests/scripts/fixture-probe.php', '$root ='),
            'loading' => array('tests/scripts/loading-probe.php', '$case ='),
            'native' => array('tests/scripts/native.php', "ini_set('display_errors'"),
            'pagination' => array('tests/scripts/pagination-probe.php', '$root ='),
            'session' => array('tests/scripts/session-probe.php', '$root ='),
            'process-worker' => array('tests/scripts/process-worker.php', 'if (!posix_setpgid'),
            'inventory' => array('tests/scripts/inventory.php', '$root ='),
            'navigation' => array('tests/scripts/navigation-probe.php', 'class NavigationProbe'),
        );
    }

    /**
     * Reject suppressed diagnostics before support loading or other startup work.
     *
     * @dataProvider entrypoints
     * @param string $file
     * @param string $boundary
     * @return void
     */
    public function testEarlyHandlersRejectSuppressedDiagnostics($file, $boundary)
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/' . $file);
        $position = strpos($source, $boundary);
        $this->assertNotFalse($position, $file);
        $prefix = substr($source, 0, $position);
        $faults = array('user' => E_USER_DEPRECATED, 'native' => E_WARNING);
        if (PHP_VERSION_ID >= 80000) {
            $faults['compile'] = E_DEPRECATED;
        }
        foreach ($faults as $fault => $severity) {
            $result = $this->probe($prefix, $fault, false);
            $this->assertSame(23, $result['exit'], $file . ': ' . $result['stdout']);
            $this->assertSame('ErrorException', $result['data']['class']);
            $this->assertSame($severity, $result['data']['severity']);
            $this->assertSame(E_ALL, $result['data']['reporting']);
            $this->assertSame($result['path'], $result['data']['file']);
        }
        $restored = $this->probe($prefix, 'user', true);
        $this->assertSame(0, $restored['exit'], $restored['stdout']);
        $this->assertSame(array('continued' => true), $restored['data']);
    }

    /**
     * Run an exact entrypoint prefix in an owned child without loading add-on code.
     *
     * @param string $prefix
     * @param string $fault
     * @param bool $restore
     * @return array
     * @throws \RuntimeException
     */
    private function probe($prefix, $fault, $restore)
    {
        $root = dirname(__DIR__, 3);
        $directory = sys_get_temp_dir() . '/low-title-startup-' . getmypid() . '-' . bin2hex(random_bytes(6));
        mkdir($directory, 0700);
        $directory = realpath($directory);
        $path = $directory . '/probe.php';
        $dependency = $directory . '/dependency.php';
        file_put_contents($dependency, '<?php function startup_control($optional = null, $required) {}');
        $statement = "@trigger_error('LowTitleStartupControl', E_USER_DEPRECATED);";
        if ($fault === 'native') {
            $statement = "@fopen(__DIR__ . '/missing', 'rb');";
        }
        if ($fault === 'compile') {
            $statement = "@require __DIR__ . '/dependency.php';";
        }
        $body = <<<'BODY'
try {
STATEMENT
    echo json_encode(array('continued' => true));
} catch (\Throwable $exception) {
    echo json_encode(array('class' => get_class($exception), 'severity' => $exception->getSeverity(), 'reporting' => error_reporting(), 'file' => $exception->getFile()));
    exit(23);
}
BODY;
        file_put_contents($path, $prefix . "\n" . ($restore ? "restore_error_handler();\n" : '') . str_replace('STATEMENT', $statement, $body));
        try {
            $command = array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', '-d', 'sys_temp_dir=' . $directory, $path);
            $process = Process::run($command, $root, array_merge(getenv(), array('TMPDIR' => $directory, 'LOW_TITLE_COVERAGE_SOURCE' => '', 'LOW_TITLE_BRANCH_REPORT' => '')));
            $this->assertFalse($process['timed_out']);
            $this->assertSame('', $process['stderr']);
            $data = json_decode($process['stdout'], true);
            $this->assertIsArray($data, $process['stdout']);

            return array('exit' => $process['exit'], 'stdout' => $process['stdout'], 'data' => $data, 'path' => $fault === 'compile' ? $dependency : $path);
        } finally {
            unlink($path);
            unlink($dependency);
            rmdir($directory);
            $this->assertDirectoryDoesNotExist($directory);
        }
    }
}
