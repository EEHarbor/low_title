<?php

namespace LowTitleTests;

class PaginationFlowTest extends TestCase
{
    /**
     * Return controller and global call frames with nullable or explicit CP URLs.
     *
     * @return array
     */
    public static function flows()
    {
        return array(array('named-default'), array('global-explicit'), array('global-default'));
    }

    /**
     * Preserve model state through the real bridge and typed pagination boundary.
     *
     * @dataProvider flows
     * @param string $case
     * @return void
     */
    public function testPaginationFlow($case)
    {
        $directory = sys_get_temp_dir() . '/low-title-pagination-' . getmypid() . '-' . bin2hex(random_bytes(6));
        mkdir($directory);
        $root = dirname(__DIR__, 3);
        try {
            $command = array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', '-d', 'sys_temp_dir=' . $directory, $root . '/tests/scripts/pagination-probe.php', $case);
            $process = Process::run($command, $root, array_merge(getenv(), array('LOW_TITLE_COVERAGE_SOURCE' => '', 'LOW_TITLE_BRANCH_REPORT' => '', 'LOW_TITLE_EE_VERSION' => '7.5.0', 'LOW_TITLE_EE_REQUEST' => 'PAGE', 'TMPDIR' => $directory)));
            $result = json_decode($process['stdout'], true);
            if (!is_array($result)) {
                $this->fail('The pagination probe returned no JSON result: ' . $process['stdout'] . $process['stderr']);
            }
            $this->assertFalse($process['timed_out']);
            $this->assertSame('', $process['stderr']);
            $this->assertSame(0, iterator_count(new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS)));
            $this->assertSame(0, $process['exit'], $process['stdout']);
            $this->assertTrue($result['ok']);
            $this->assertTrue($result['same_model']);
            $this->assertSame(array(array('limit', 25), array('offset', 0)), $result['model_calls']);
            $this->assertSame(array('pagination' => '<pagination>'), $result['data']);
            $this->assertSame(array(100), $result['totals']);
            $path = $case === 'global-explicit' ? 'entries' : 'addons/settings/low_title/entries';
            if ($case === 'global-default') {
                $path = 'addons/settings/low_title';
            }
            $this->assertSame(array(array('perPage', 25), array('currentPage', 1), array('displayPageLinks', 5), array('queryStringVariable', 'page'), array('render', array('path' => $path, 'variables' => array()))), $result['pagination_calls']);
        } finally {
            $this->removeDirectory($directory);
            $this->assertDirectoryDoesNotExist($directory);
        }
    }

    /**
     * Remove only the unique owned tree without following symbolic links.
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
