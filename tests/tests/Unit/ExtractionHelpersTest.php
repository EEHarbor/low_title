<?php

namespace LowTitleTests;

class ExtractionHelpersTest extends TestCase
{
    /**
     * Compare the shipped binary manifest and extraction with Phar's archive view.
     *
     * @return void
     */
    public function testShippedManifestAndContents()
    {
        $path = dirname((new \ReflectionClass(\Low_title::class))->getFileName()) . '/low_title.phar';
        $stream = fopen($path, 'rb');
        try {
            fseek($stream, \Extract_low_title::LEN);
            $length = unpack('Vlength', fread($stream, 4))['length'];
            $manifest = fread($stream, $length);
            $this->assertSame($length, strlen($manifest));
            $dataStart = ftell($stream);
            $result = \Extract_low_title::_unpack($manifest);
            $archive = new \Phar($path);
            $members = array();
            foreach (new \RecursiveIteratorIterator($archive) as $member) {
                $relative = substr($member->getPathname(), strpos($member->getPathname(), '.phar/') + 6);
                $members[$relative] = $member;
            }
            $expected = array_keys($members);
            $actual = array_keys($result['m']);
            sort($expected);
            sort($actual);
            $this->assertSame($expected, $actual);
            $this->assertCount(count($members), $result['m']);
            $offset = 0;
            $compression = 0;
            foreach ($result['m'] as $relative => $entry) {
                $member = $members[$relative];
                $this->assertSame($member->getSize(), $entry[0], $relative);
                $this->assertSame($member->getCompressedSize(), $entry[2], $relative);
                $this->assertSame(sprintf('%u', $member->getCRC32()), $entry[3], $relative);
                $this->assertSame($offset, $entry[7], $relative);
                fseek($stream, $dataStart + $entry[7]);
                $this->assertSame($member->getContent(), \Extract_low_title::extractFile($relative, $entry, $stream), $relative);
                $offset += $entry[2];
                $compression |= $entry[4] & \Extract_low_title::MASK;
            }
            $this->assertSame($compression, $result['c']);
        } finally {
            fclose($stream);
        }
    }

    /**
     * Return a minimal member and a manifest with aliases, metadata, and compression.
     *
     * @return array
     */
    public static function manifestShapes()
    {
        return array(array(false), array(true));
    }

    /**
     * Preserve binary offsets and compression flags across optional manifest data.
     *
     * @dataProvider manifestShapes
     * @param bool $metadata
     * @return void
     */
    public function testManifestMetadata($metadata)
    {
        $alias = $metadata ? 'fixture.phar' : '';
        $global = $metadata ? serialize(array('owner' => 'fixture')) : '';
        $flags = $metadata ? array(0, \Extract_low_title::GZ, \Extract_low_title::BZ2) : array(0);
        $manifest = pack('VvVV', count($flags), 0x0011, 0, strlen($alias)) . $alias . pack('V', strlen($global)) . $global;
        $expected = array('c' => 0, 'm' => array());
        $offset = 0;
        foreach ($flags as $index => $flag) {
            $name = 'entry-' . $index . '.bin';
            $data = str_repeat('payload', $index + 1);
            $stored = $data;
            if ($flag === \Extract_low_title::GZ) {
                $stored = gzdeflate($data);
            }
            if ($flag === \Extract_low_title::BZ2) {
                $stored = bzcompress($data);
            }
            $entryMetadata = $metadata ? serialize(array('entry' => $index)) : '';
            $permissions = $flag | 0644;
            $manifest .= pack('V', strlen($name)) . $name . pack('VVVVVV', strlen($data), 1234567890, strlen($stored), crc32($data), $permissions, strlen($entryMetadata)) . $entryMetadata;
            $expected['m'][$name] = array(strlen($data), 1234567890, strlen($stored), sprintf('%u', crc32($data) & 0xffffffff), $permissions, strlen($entryMetadata), 7 => $offset);
            $expected['c'] |= $flag;
            $offset += strlen($stored);
        }
        $this->assertSame($expected, \Extract_low_title::_unpack($manifest));
    }

    /**
     * Supply empty, partial, exact and multiple extraction chunks and compression.
     *
     * @return array
     */
    public static function payloads()
    {
        $cases = array();
        foreach (array(0, 1, 8191, 8192, 8193, 16384, 16385) as $length) {
            foreach (array(0, \Extract_low_title::GZ, \Extract_low_title::BZ2) as $compression) {
                $cases[] = array($length, $compression);
            }
        }
        return $cases;
    }

    /**
     * Preserve byte-exact file contents for each supported compression and boundary.
     *
     * @dataProvider payloads
     * @param int $length
     * @param int $compression
     * @return void
     */
    public function testPayload($length, $compression)
    {
        $data = '';
        for ($index = 0; strlen($data) < $length; $index++) {
            $data .= hash('sha256', pack('N', $index), true);
        }
        $data = substr($data, 0, $length);
        $stored = $data;
        if ($compression === \Extract_low_title::GZ) {
            $stored = gzdeflate($data);
        }
        if ($compression === \Extract_low_title::BZ2) {
            $stored = bzcompress($data);
        }
        $stream = fopen('php://memory', 'r+');
        try {
            fwrite($stream, $stored . 'tail');
            rewind($stream);
            $entry = array(strlen($data), 0, strlen($stored), sprintf('%u', crc32($data) & 0xffffffff), $compression, 0, 0, 0);
            $this->assertSame($data, \Extract_low_title::extractFile('payload.bin', $entry, $stream));
            $this->assertSame(strlen($stored), ftell($stream));
            $this->assertSame('tail', fread($stream, 4));
        } finally {
            fclose($stream);
        }
    }

    /**
     * Prefer the caller environment and use the Unix fallback for missing values.
     *
     * @return void
     */
    public function testTemporaryDirectory()
    {
        $caller = getenv('TMPDIR');
        $directory = sys_get_temp_dir() . '/low-title-owned-' . getmypid() . '-' . bin2hex(random_bytes(4));
        mkdir($directory);
        try {
            putenv('TMPDIR=' . $directory);
            $this->assertSame($directory, \Extract_low_title::tmpdir());
            putenv('TMPDIR=');
            $this->assertSame(realpath('/tmp'), \Extract_low_title::tmpdir());
            putenv('TMPDIR');
            $this->assertSame(realpath('/tmp'), \Extract_low_title::tmpdir());
        } finally {
            putenv($caller === false ? 'TMPDIR' : 'TMPDIR=' . $caller);
            rmdir($directory);
        }
    }

    /**
     * Return empty, flat, nested, and hidden extraction trees.
     *
     * @return array
     */
    public static function cleanupTrees()
    {
        return array(array('empty'), array('flat'), array('nested'), array('hidden'));
    }

    /**
     * Remove the complete owned tree and restore the caller directory.
     *
     * @dataProvider cleanupTrees
     * @param string $shape
     * @return void
     */
    public function testCleanup($shape)
    {
        $directory = sys_get_temp_dir() . '/low-title-cleanup-' . getmypid() . '-' . bin2hex(random_bytes(4));
        mkdir($directory);
        if ($shape === 'flat') {
            file_put_contents($directory . '/payload', 'contents');
            mkdir($directory . '/empty');
        }
        if ($shape === 'nested') {
            mkdir($directory . '/nested');
            file_put_contents($directory . '/nested/payload', 'contents');
        }
        if ($shape === 'hidden') {
            file_put_contents($directory . '/.payload', 'contents');
        }
        $caller = getcwd();
        try {
            \Extract_low_title::_removeTmpFiles($directory, $caller);
            $this->assertDirectoryDoesNotExist($directory);
            $this->assertSame($caller, getcwd());
        } finally {
            chdir($caller);
            $this->clean($directory);
            $this->assertDirectoryDoesNotExist($directory);
            $this->assertSame($caller, getcwd());
        }
    }

    /**
     * Return checksum and declared-size failures from corrupt extraction streams.
     *
     * @return array
     */
    public static function invalidPayloads()
    {
        return array(
            array('checksum', 'Invalid internal .phar file (checksum error)'),
            array('size', 'Invalid internal .phar file (size error 7 != 8)'),
            array('truncated', 'Invalid internal .phar file (size error 6 != 7)'),
        );
    }

    /**
     * Reject corrupt contents with the stub's intentional diagnostic and exit.
     *
     * @dataProvider invalidPayloads
     * @param string $case
     * @param string $expected
     * @return void
     */
    public function testInvalidPayload($case, $expected)
    {
        $directory = sys_get_temp_dir() . '/low-title-integrity-' . getmypid() . '-' . bin2hex(random_bytes(4));
        mkdir($directory);
        $root = dirname(__DIR__, 3);
        try {
            $command = array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', '-d', 'sys_temp_dir=' . $directory, $root . '/tests/scripts/native.php', 'extract:' . $case);
            $process = Process::run($command, $root, array_merge(getenv(), array('LOW_TITLE_COVERAGE_SOURCE' => '', 'LOW_TITLE_BRANCH_REPORT' => '', 'TMPDIR' => $directory)));
            $this->assertFalse($process['timed_out']);
            $this->assertSame('', $process['stderr']);
            $this->assertSame($expected, $process['stdout']);
            $this->assertSame(0, $process['exit']);
        } finally {
            $this->clean($directory);
            $this->assertDirectoryDoesNotExist($directory);
        }
    }

    /**
     * Remove only a unique fixture tree after either successful or failed cleanup.
     *
     * @param string $directory
     * @return void
     */
    private function clean($directory)
    {
        if (!is_dir($directory)) {
            return;
        }
        foreach (new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS) as $member) {
            if ($member->isDir() && !$member->isLink()) {
                $this->clean($member->getPathname());
                continue;
            }
            unlink($member->getPathname());
        }
        rmdir($directory);
    }
}
