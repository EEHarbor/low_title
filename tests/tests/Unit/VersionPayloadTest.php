<?php

namespace LowTitleTests;

class VersionPayloadTest extends TestCase
{
    /**
     * Return fresh and request-cached schema states plus optional licensing data.
     *
     * @return array
     */
    public static function payloads()
    {
        return array(
            array('fresh-valid', false, 0, 0),
            array('fresh-corrupt', true, 0, 0),
            array('cached-valid', false, 0, 0),
            array('cached-corrupt', true, 0, 0),
            array('pro-site', false, '3.0.0', 'SITE-KEY'),
            array('pro-null', false, null, 0),
            array('site-zero', false, 0, 0),
        );
    }

    /**
     * Preserve the public encrypted-payload input without replacing real encryption.
     *
     * @dataProvider payloads
     * @param string $case
     * @param bool $corrupt
     * @param int|string|null $pro
     * @param int|string $siteLicense
     * @return void
     */
    public function testPayloadUsesVerifiedSchemaAndOptionalMetadata($case, $corrupt, $pro, $siteLicense)
    {
        $result = $this->probe($case);
        $this->assertSame(0, $result['exit'], $result['stdout']);
        $data = $result['data'];
        $this->assertTrue($data['ok']);
        $this->assertSame($corrupt, $data['verification']);
        if (strpos($case, 'cached-') === 0) {
            $this->assertSame($corrupt, $data['cache_prime_verification']);
            $this->assertSame(array(array('table_exists', 'eeharbor_licenses'), array('field_exists', 'ignore_site', 'eeharbor_licenses'), array('query', "SHOW KEYS FROM exp_eeharbor_licenses WHERE Key_name = 'PRIMARY'")), $data['cache_prime_calls']);
        }
        $this->assertCount(1, $data['encryptions']);
        $this->assertSame(array(
            'api' => '1', 'l' => 'ABC-123', 'a' => 'low_title', 'v' => '1.2.3',
            'e' => APP_VER, 'p' => PHP_VERSION, 'd' => 'https://example.test',
            'c' => $corrupt, 's' => 19, 'pi' => $pro, 'pl' => $siteLicense, 'lc' => false,
        ), json_decode($data['encryptions'][0]['data'], true));
        $this->assertTrue($data['encryptions'][0]['success']);
        $this->assertSame(512, $data['encryptions'][0]['bytes']);
        $this->assertSame(array('get_foot', 'add_to_foot'), array_column($data['cp_calls'], 0));
        $this->assertMatchesRegularExpression('/\?[a-f0-9]{1024}/', $data['cp_calls'][1][1]);
        if (strpos($case, 'cached-') === 0) {
            $this->assertSame(array(), $data['constructor_calls']);
        }
    }

    /**
     * Retain a real RSA size failure without emitting an invalid update pixel.
     *
     * @return void
     */
    public function testOversizedRealPayloadDoesNotEmitBrokenPixel()
    {
        $result = $this->probe('oversized');
        $this->assertCount(1, $result['data']['encryptions']);
        $this->assertFalse($result['data']['encryptions'][0]['success']);
        $this->assertSame(0, $result['exit'], $result['stdout']);
        $this->assertSame(array(), array_values(array_filter($result['data']['cp_calls'], static function ($call) {
            return $call[0] === 'add_to_foot';
        })), 'Failed encryption must not create a footer pixel with empty ciphertext.');
    }

    /**
     * Keep API-spy definitions and OpenSSL error state inside a fresh owned child.
     *
     * @param string $case
     * @return array
     * @throws \RuntimeException
     */
    private function probe($case)
    {
        $root = dirname(__DIR__, 3);
        $directory = sys_get_temp_dir() . '/low-title-payload-' . getmypid() . '-' . bin2hex(random_bytes(6));
        mkdir($directory, 0700);
        $command = array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', '-d', 'sys_temp_dir=' . $directory, $root . '/tests/scripts/version-payload-probe.php', $case);
        try {
            $process = Process::run($command, $root, array_merge($_ENV, array('TMPDIR' => $directory, 'LOW_TITLE_EE_VERSION' => APP_VER, 'LOW_TITLE_COVERAGE_SOURCE' => '', 'LOW_TITLE_BRANCH_REPORT' => '')));
            $this->assertFalse($process['timed_out']);
            $this->assertSame('', $process['stderr']);
            $data = json_decode($process['stdout'], true);
            $this->assertIsArray($data, $process['stdout']);
            $this->assertSame($case, $data['case']);
            $this->assertSame(hash_file('sha256', $root . '/low_title/low_title.phar'), $data['archive_sha256']);
            $this->assertSame('phar://' . $root . '/low_title/low_title.phar/FluxCapacitor/Conduit/Version.php', $data['source_file']);
            $this->assertSame($root . '/tests/Support/Encryption.php', $data['spy_file']);
            $this->assertSame(array(), iterator_to_array(new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS)));

            return array('exit' => $process['exit'], 'stdout' => $process['stdout'], 'data' => $data);
        } finally {
            rmdir($directory);
            $this->assertDirectoryDoesNotExist($directory);
        }
    }
}
