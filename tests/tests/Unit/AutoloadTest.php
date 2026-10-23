<?php

namespace LowTitleTests;

class AutoloadTest extends TestCase
{
    /**
     * Return native stream, opcode-cache, fallback, and missing-source cases.
     *
     * @return array
     */
    public static function loaders()
    {
        return array(array('unrelated'), array('sibling-namespace'), array('missing-archive'), array('missing-class'), array('native'), array('opcache'), array('extraction'));
    }

    /**
     * Exercise shipped loaders in isolated processes and temporary directories.
     *
     * @dataProvider loaders
     * @param string $case
     * @return void
     */
    public function testShippedAutoloader($case)
    {
        $directory = sys_get_temp_dir() . '/low-title-loader-' . getmypid() . '-' . bin2hex(random_bytes(6));
        mkdir($directory);
        $root = dirname(__DIR__, 3);
        $command = array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', '-d', 'sys_temp_dir=' . $directory, '-d', 'opcache.enable_cli=' . ($case === 'opcache' ? '1' : '0'), '-d', 'opcache.validate_permission=' . ($case === 'opcache' ? '1' : '0'), $root . '/tests/scripts/autoload-probe.php', $case, $directory);
        try {
            $process = Process::run($command, $root, array_merge($_ENV, array('LOW_TITLE_COVERAGE_SOURCE' => '', 'LOW_TITLE_BRANCH_REPORT' => '', 'TMPDIR' => $directory)));
            $stdout = $process['stdout'];
            $stderr = $process['stderr'];
            $exit = $process['exit'];
            $result = json_decode($stdout, true);
            if (!is_array($result)) {
                $this->fail('The loader probe returned no JSON result: ' . $stdout . $stderr);
            }
            if ($case === 'extraction') {
                $this->assertSame($directory, $result['environment_tmpdir']);
                $this->assertSame($directory . '/pharextract/low_title', $result['extraction_directory']);
                unset($result['environment_tmpdir'], $result['extraction_directory']);
            }
            $this->assertSame(0, $exit, $stdout . $stderr);
            $this->assertSame('', $stderr);
            $this->assertSame(array('ok' => true), $result);
        } finally {
            $this->removeDirectory($directory);
            $this->assertDirectoryDoesNotExist($directory);
        }
    }

    /**
     * Remove only the unique test-owned directory without following symlinks.
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
    /**
     * Reject unrelated class namespaces without touching the archive.
     *
     * @return void
     */
    public function testUnrelatedNamespaceIsIgnored()
    {
        foreach (spl_autoload_functions() as $loader) {
            if ($loader instanceof \Closure && (new \ReflectionFunction($loader))->getFileName() === dirname(__DIR__, 3) . '/low_title/autoload.php') {
                $this->assertNull($loader('OtherVendor\\Example'));
                return;
            }
        }
        $this->fail('The shipped autoloader was not registered');
    }

    /**
     * Return an actionable missing-class path when a FluxCapacitor member is absent.
     *
     * @return void
     */
    public function testMissingClassThrowsDescriptiveException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Class at location phar://' . PATH_THIRD . 'low_title/low_title.phar/FluxCapacitor/NotShipped.php not found.');
        class_exists('Low\\Title\\FluxCapacitor\\NotShipped');
    }

}
