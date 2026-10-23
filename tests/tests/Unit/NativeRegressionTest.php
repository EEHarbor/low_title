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
        foreach (array('Base/Mcp', 'Base/Tab', 'Base/Ft') as $class) {
            $cases[] = array('construct:' . $class, 'CP');
        }
        $cases[] = array('extension-menu', 'CP');
        $cases[] = array('archive-index');
        foreach (array('2.5.0', '3.5.0', '4.3.0', '5.5.0', '6.5.0', '7.5.0') as $version) {
            $cases[] = array('bridge-version', 'PAGE', $version);
        }
        foreach (array('2.5.0', '5.5.0', '6.5.0') as $version) {
            $cases[] = array('updater-uninstall', 'PAGE', $version);
        }
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
     * @param string $request
     * @param string $version
     * @return void
     */
    public function testNativeSourceRemainsCompatible($case, $request = 'PAGE', $version = '7.5.0')
    {
        $process = $this->runNative($case, $request, $version);
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
     * @param string $request
     * @param string $version
     * @return array
     */
    private function runNative($case, $request = 'PAGE', $version = '7.5.0')
    {
        $directory = sys_get_temp_dir() . '/low-title-native-' . getmypid() . '-' . bin2hex(random_bytes(6));
        mkdir($directory);
        $command = array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', '-d', 'sys_temp_dir=' . $directory, dirname(__DIR__, 2) . '/scripts/native.php', $case);
        try {
            return Process::run($command, dirname(__DIR__, 3), array_merge($_ENV, array('LOW_TITLE_COVERAGE_SOURCE' => '', 'LOW_TITLE_BRANCH_REPORT' => '', 'LOW_TITLE_EE_REQUEST' => $request, 'LOW_TITLE_EE_VERSION' => $version, 'TMPDIR' => $directory)));
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
