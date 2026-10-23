<?php

namespace LowTitleTests;

class FixtureInstallationTest extends TestCase
{
    /**
     * Return contexts that require a distinct installation path or EE version.
     *
     * @return array
     */
    public static function installations()
    {
        return array(array('legacy-inline-view'), array('legacy-session'), array('legacy-cache'));
    }

    /**
     * Execute unchanged shipped archive bytes in a disposable installation.
     *
     * @dataProvider installations
     * @param string $case
     * @return void
     */
    public function testIsolatedInstallation($case)
    {
        $directory = sys_get_temp_dir() . '/low-title-install-' . getmypid() . '-' . bin2hex(random_bytes(6));
        mkdir($directory);
        mkdir($directory . '/low_title');
        mkdir($directory . '/low_title/views');
        $root = dirname(__DIR__, 3);
        $archive = $root . '/low_title/low_title.phar';
        $copy = $directory . '/low_title/low_title.phar';
        copy($archive, $copy);
        $this->assertSame(hash_file('sha256', $archive), hash_file('sha256', $copy));
        $command = array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', '-d', 'sys_temp_dir=' . $directory, $root . '/tests/scripts/fixture-probe.php', $case, $directory);
        try {
            $process = proc_open($command, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, $root, array_merge($_ENV, array('LOW_TITLE_COVERAGE_SOURCE' => '', 'LOW_TITLE_BRANCH_REPORT' => '', 'TMPDIR' => $directory)));
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exit = proc_close($process);
            $this->assertSame(0, $exit, $stdout . $stderr);
            $this->assertSame('', $stderr);
            $this->assertSame(array('ok' => true, 'case' => $case), json_decode($stdout, true));
        } finally {
            if (is_file($directory . '/low_title/views/dynamic.php')) {
                unlink($directory . '/low_title/views/dynamic.php');
            }
            unlink($copy);
            rmdir($directory . '/low_title/views');
            rmdir($directory . '/low_title');
            rmdir($directory);
        }
    }
}
