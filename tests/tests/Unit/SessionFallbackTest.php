<?php

namespace LowTitleTests;

class SessionFallbackTest extends TestCase
{
    /**
     * Supply supported session configurations and missing or non-directory paths.
     *
     * @return array
     */
    public static function paths()
    {
        return array(array('plain', true), array('depth', true), array('mode', true), array('tmp-file', true), array('tmp-readonly', true), array('missing', false), array('file', false), array('empty', true));
    }

    /**
     * Preserve valid fallback paths and reject absent or non-directory sessions.
     *
     * @dataProvider paths
     * @param string $case
     * @param bool $valid
     * @return void
     */
    public function testSessionFallback($case, $valid)
    {
        $directory = sys_get_temp_dir() . '/low-title-session-' . getmypid() . '-' . bin2hex(random_bytes(6));
        mkdir($directory);
        $root = dirname(__DIR__, 3);
        try {
            $command = array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', '-d', 'sys_temp_dir=' . $directory, '-d', 'session.save_handler=files', '-d', 'session.use_cookies=0', '-d', 'session.cache_limiter=', $root . '/tests/scripts/session-probe.php', $case, $directory);
            $process = Process::run($command, $root, array_merge(getenv(), array('TMPDIR' => $directory, 'LOW_TITLE_BRANCH_REPORT' => '', 'LOW_TITLE_COVERAGE_SOURCE' => '')));
            $result = json_decode($process['stdout'], true);
            if (!is_array($result)) {
                $this->fail('The session probe returned no JSON: ' . $process['stdout'] . $process['stderr']);
            }
            $this->assertFalse($process['timed_out']);
            $this->assertSame('', $process['stderr']);
            $this->assertSame($directory, $result['fixture_root']);
            $this->assertSame(0, $result['fixtures_remaining']);
            $this->assertSame(0, iterator_count(new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS)));
            $session = $directory . '/session';
            if ($case === 'depth') {
                $session = '0;' . $session;
            }
            if ($case === 'mode') {
                $session = '0;0600;' . $session;
            }
            if ($case === 'missing') {
                $session = $directory . '/missing-session';
            }
            if ($case === 'file') {
                $session = $directory . '/session-file';
            }
            if ($case === 'empty') {
                $session = '';
            }
            $this->assertSame($session, $result['session_value']);
            $temporary = $directory . '/missing-temp';
            if ($case === 'tmp-file') {
                $temporary = $directory . '/temp-file';
            }
            if ($case === 'tmp-readonly') {
                $temporary = $directory . '/readonly-temp';
            }
            $this->assertSame($temporary, $result['tmpdir']);
            $this->assertSame($case === 'tmp-readonly', $result['tmp_is_directory']);
            $this->assertSame($case === 'tmp-file', $result['tmp_is_writable']);
            $this->assertSame('source-extraction', $result['phase'], $process['stdout']);
            $this->assertSame($valid ? array('exists' => true, 'mode' => 0600) : null, $result['native_session'], $process['stdout']);
            $this->assertSame(0, $process['exit'], $process['stdout']);
            if (!$valid) {
                $this->assertFalse($result['returned']);
                $this->assertNull($result['extraction_directory']);
                $this->assertSame('Could not locate temporary directory to extract phar', $result['output']);

                return;
            }
            $this->assertSame('', $result['output'], $process['stdout']);
            $this->assertTrue($result['returned'], $process['stdout']);
            $extraction = $case === 'empty' ? $directory : $directory . '/session';
            $this->assertSame($extraction . '/pharextract/low_title', $result['extraction_directory']);
        } finally {
            $this->removeDirectory($directory);
            $this->assertDirectoryDoesNotExist($directory);
        }
    }

    /**
     * Remove only the unique owned tree, including partially completed fixtures.
     *
     * @param string $directory
     * @return void
     */
    private function removeDirectory($directory)
    {
        chmod($directory, 0700);
        foreach (new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS) as $entry) {
            if ($entry->isDir() && !$entry->isLink()) {
                $this->removeDirectory($entry->getPathname());
                continue;
            }
            unlink($entry->getPathname());
        }
        rmdir($directory);
    }
}
