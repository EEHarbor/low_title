<?php

namespace LowTitleTests;

class NativeRegressionTest extends TestCase
{
    /**
     * Return independently isolated native source loading and regression probes.
     *
     * @return array
     */
    public static function nativeCases()
    {
        $cases = array(array('construct:Base/Ext'), array('construct:Base/Mod'), array('construct:Base/Mcp'), array('construct:Base/Tab'), array('construct:Base/Ft'), array('construct:Base/Upd'), array('construct:Conduit/GlobalFieldSettings'), array('construct:Conduit/Version'), array('constructor'), array('entry-property'), array('static-info'), array('null-title'), array('missing-pages-uris'));
        $inventory = json_decode(file_get_contents(dirname(__DIR__, 2) . '/source-inventory.json'), true);
        foreach ($inventory['files'] as $origin => $file) {
            $prefix = 'low_title/low_title.phar/';
            if (strpos($origin, $prefix . 'FluxCapacitor/') === 0) {
                $cases[] = array('load:' . substr($origin, strlen($prefix), -4));
            }
        }

        return $cases;
    }

    /**
     * Require native source paths to remain free of fatal errors and diagnostics.
     *
     * @dataProvider nativeCases
     * @param string $case
     * @return void
     */
    public function testNativeSourceRemainsCompatible($case)
    {
        $process = $this->runNative($case);
        $result = json_decode($process['stdout'], true);
        $this->assertSame(0, $process['exit'], $process['stdout'] . $process['stderr']);
        $this->assertSame('', $process['stderr']);
        $this->assertIsArray($result);
        $this->assertTrue($result['ok'], $process['stdout']);
    }

    /**
     * Verify that the strict harness also rejects diagnostics hidden by @.
     *
     * @return void
     */
    public function testSuppressedDeprecationsAreDetected()
    {
        $process = $this->runNative('suppressed-diagnostic');
        $result = json_decode($process['stdout'], true);
        $this->assertSame('', $process['stderr']);
        $this->assertSame(1, $process['exit']);
        $this->assertSame(E_USER_DEPRECATED, $result['severity']);
        $this->assertSame('strict harness diagnostic probe', $result['message']);
    }

    /**
     * Run one native probe with both PHP and PHAR temporary paths owned by this test.
     *
     * @param string $case
     * @return array
     */
    private function runNative($case)
    {
        $directory = sys_get_temp_dir() . '/low-title-native-' . getmypid() . '-' . bin2hex(random_bytes(6));
        mkdir($directory);
        $command = array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', '-d', 'sys_temp_dir=' . $directory, dirname(__DIR__, 2) . '/scripts/native.php', $case);
        try {
            $process = proc_open($command, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, dirname(__DIR__, 3), array_merge($_ENV, array('LOW_TITLE_COVERAGE_SOURCE' => '', 'LOW_TITLE_BRANCH_REPORT' => '', 'TMPDIR' => $directory)));
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            return array('exit' => proc_close($process), 'stdout' => $stdout, 'stderr' => $stderr);
        } finally {
            $this->removeDirectory($directory);
        }
    }

    /**
     * Remove only the test-owned temporary tree without following symlinks.
     *
     * @param string $directory
     * @return void
     */
    private function removeDirectory($directory)
    {
        foreach (new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS) as $file) {
            if ($file->isDir() && !$file->isLink()) {
                $this->removeDirectory($file->getPathname());
                continue;
            }
            unlink($file->getPathname());
        }
        rmdir($directory);
    }
}
