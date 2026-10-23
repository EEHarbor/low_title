<?php

namespace LowTitleTests;

class NativeLoadingTest extends TestCase
{
    /**
     * Return legacy singleton and native fieldtype loader contracts.
     *
     * @return array
     */
    public static function loadingContexts()
    {
        return array(
            array('legacy-config'),
            array('legacy-defined'),
            array('fieldtype-system'),
            array('fieldtype-app'),
            array('fieldtype-priority'),
            array('fieldtype-defined-version'),
            array('fieldtype-installer'),
            array('fieldtype-installer-ancestor'),
            array('fieldtype-predeclared'),
        );
    }

    /**
     * Load shipped paths without bootstrap-owned singleton functions or parents.
     *
     * @dataProvider loadingContexts
     * @param string $case
     * @return void
     */
    public function testNativeLoadingContext($case)
    {
        $directory = sys_get_temp_dir() . '/low-title-native-loading-' . getmypid() . '-' . bin2hex(random_bytes(6));
        mkdir($directory);
        $directory = realpath($directory);
        $root = dirname(__DIR__, 3);
        try {
            $command = array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', '-d', 'sys_temp_dir=' . $directory, $root . '/tests/scripts/loading-probe.php', $case, $directory);
            $process = Process::run($command, $root, array_merge(getenv(), array('LOW_TITLE_COVERAGE_SOURCE' => '', 'LOW_TITLE_BRANCH_REPORT' => '', 'TMPDIR' => $directory)));
            $result = json_decode($process['stdout'], true);
            if (!is_array($result)) {
                $this->fail('The loading probe returned no JSON result: ' . $process['stdout'] . $process['stderr']);
            }
            $this->assertFalse($process['timed_out']);
            $this->assertSame('', $process['stderr']);
            $this->assertSame($directory, $result['fixture_root']);
            $this->assertSame(0, $result['fixtures_remaining']);
            $this->assertSame(0, $process['exit'], $process['stdout']);
            $this->assertTrue($result['ok'], $process['stdout']);
            if (strpos($case, 'legacy-') === 0) {
                $this->assertSame('2.5.0', $result['ee_version']);
                $this->assertSame('EE2', $result['parent']);
                return;
            }
            $expected = $directory . '/ordinary-parent/expressionengine/fieldtypes/EE_Fieldtype.php';
            if ($case === 'fieldtype-system' || $case === 'fieldtype-priority') {
                $expected = $directory . '/system/ee/legacy/fieldtypes/EE_Fieldtype.php';
            }
            if ($case === 'fieldtype-installer-ancestor') {
                $expected = $directory . '/installer-parent/expressionengine/fieldtypes/EE_Fieldtype.php';
            }
            if ($case === 'fieldtype-predeclared') {
                $expected = $root . '/tests/Support/Fieldtype.php';
            }
            $version = $case === 'fieldtype-defined-version' ? 'fixture-version' : json_decode(file_get_contents($root . '/low_title/addon.json'))->version;
            $this->assertSame($expected, $result['loaded_from']);
            $this->assertSame($version, $result['version']);
        } finally {
            $this->removeDirectory($directory);
            $this->assertDirectoryDoesNotExist($directory);
        }
    }

    /**
     * Remove the unique owned fixture tree if a native child stops before cleanup.
     *
     * @param string $directory
     * @return void
     */
    private function removeDirectory($directory)
    {
        foreach (new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS) as $member) {
            if ($member->isDir() && !$member->isLink()) {
                $this->removeDirectory($member->getPathname());
                continue;
            }
            unlink($member->getPathname());
        }
        rmdir($directory);
    }
}
