<?php

namespace LowTitleTests;

use Low\Title\FluxCapacitor\Conduit\StaticCache;

require_once dirname(__DIR__, 2) . '/Support/BaseSubjects.php';

class VersionTest extends TestCase
{
    /**
     * Allocate independent licensing database and request state.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        StaticCache::clear();
    }

    /**
     * Clear global license cache after each context.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        StaticCache::clear();
        parent::tearDown();
    }

    /**
     * Return existing-license and supplied-data cases.
     *
     * @return array
     */
    public static function keys()
    {
        return array(array(0, array('license_key' => 'NEW')), array(1, array('license_key' => 'NEW')), array(0, array('license_key' => 'NEW', 'site_id' => 2, 'addon' => 'Other')));
    }

    /**
     * Save licenses by their current site/add-on identity and cache existence checks.
     *
     * @dataProvider keys
     * @param int $count
     * @param array $data
     * @return void
     */
    public function testSaveLicenseKey($count, $data)
    {
        $version = (new \ReflectionClass(VersionSubject::class))->newInstanceWithoutConstructor();
        $this->environment->db->count = $count;
        $this->assertSame((bool) $count, $version->keyExistsForAddon());
        $calls = count($this->environment->db->calls);
        $this->assertSame((bool) $count, $version->keyExistsForAddon());
        $this->assertCount($calls, $this->environment->db->calls);
        $version->saveLicenseKey($data);
        $expected = $data + array('site_id' => 1, 'addon' => 'Low\\Title');
        $this->assertContains(array($count ? 'update' : 'insert', 'eeharbor_licenses', $expected), $this->environment->db->calls);
        $version->deleteLicenseKey();
        $this->assertSame(array(array('where', 'site_id', 1), array('where', 'addon', 'Low\\Title'), array('delete', 'eeharbor_licenses')), array_slice($this->environment->db->calls, -3));
    }

    /**
     * Return missing and legacy license-row values.
     *
     * @return array
     */
    public static function licenses()
    {
        return array(array(array(), '', false), array(array(array('license_key' => "ABC-123\n !<>", 'ignore_site' => '1')), 'ABC-123', '1'), array(array(array('license_key' => '', 'ignore_site' => 0)), '', 0));
    }

    /**
     * Read and sanitize license keys while retaining the stored ignored-site flag.
     *
     * @dataProvider licenses
     * @param array $rows
     * @param string $key
     * @param mixed $ignored
     * @return void
     */
    public function testReadLicense($rows, $key, $ignored)
    {
        $version = (new \ReflectionClass(VersionSubject::class))->newInstanceWithoutConstructor();
        $this->environment->db->results = array($rows, $rows);
        $this->assertSame($key, $version->getLicenseKey());
        $this->assertSame($ignored, $version->getIgnoreSite());
        $this->assertSame(array('get', 'eeharbor_licenses', 1), $this->environment->db->calls[2]);
    }

    /**
     * Return schema creation, missing-column, and primary-key validity cases.
     *
     * @return array
     */
    public static function schemas()
    {
        return array(array(false, false, array(), true), array(true, true, array(array('Column_name' => 'license_key', 'Non_unique' => '0')), false), array(true, true, array(array('Column_name' => 'site_id', 'Non_unique' => '0')), true), array(true, true, array(array('Column_name' => 'license_key', 'Non_unique' => '1')), true), array(true, true, array(array('Column_name' => 'license_key', 'Non_unique' => '0'), array('Column_name' => 'addon', 'Non_unique' => '0')), true));
    }

    /**
     * Create missing licensing storage and recognize only its required primary key.
     *
     * @dataProvider schemas
     * @param bool $tableExists
     * @param bool $fieldExists
     * @param array $keys
     * @param bool $corrupt
     * @return void
     */
    public function testVerifyLicenseSchema($tableExists, $fieldExists, $keys, $corrupt)
    {
        $version = (new \ReflectionClass(VersionSubject::class))->newInstanceWithoutConstructor();
        $this->environment->db->tableExists = $tableExists;
        $this->environment->db->fieldExists = $fieldExists;
        $this->environment->db->data_cache = array('table_names' => array('stale'));
        $this->environment->db->results = array($keys);
        $this->environment->dbforge = new Recorder;
        foreach (array('add_field', 'add_key', 'create_table', 'add_column') as $method) {
            $this->environment->dbforge->returns[$method] = null;
        }
        $this->assertSame($corrupt, $version->verifyLicenseTable());
        $this->assertArrayNotHasKey('table_names', $this->environment->db->data_cache);
        $this->assertSame(!$tableExists, in_array(array('create_table', 'eeharbor_licenses'), $this->environment->dbforge->calls, true));
        $this->assertSame(!$fieldExists, (bool) array_filter($this->environment->dbforge->calls, function ($call) { return $call[0] === 'add_column'; }));
        $count = count($this->environment->db->calls);
        $this->assertSame($corrupt, $version->verifyLicenseTable());
        $this->assertCount($count, $this->environment->db->calls);
    }

    /**
     * Generate an encrypted update pixel locally without making a network request.
     *
     * @return void
     */
    public function testVersionPixelIsEncryptedAndAddedOnce()
    {
        StaticCache::put('app_settings', array('version' => '1.2.3'));
        $this->environment->config->config['base_url'] = 'https://example.test';
        $this->environment->cp = new Recorder;
        $this->environment->cp->returns = array('get_foot' => array(), 'add_to_foot' => null);
        $this->environment->db->results = array(array(), array());
        $version = (new \ReflectionClass(VersionSubject::class))->newInstanceWithoutConstructor();
        $version->flux = new \Low\Title\FluxCapacitor\FluxCapacitor;
        $version->check();
        $this->assertCount(2, $this->environment->cp->calls);
        $this->assertSame('get_foot', $this->environment->cp->calls[0][0]);
        $this->assertSame('add_to_foot', $this->environment->cp->calls[1][0]);
        $html = $this->environment->cp->calls[1][1];
        $this->assertStringContainsString('<div id="low_title__up"', $html);
        $this->assertMatchesRegularExpression('/\?[a-f0-9]{1024}/', $html);
        $this->assertStringNotContainsString('https://example.test', $html);
        $this->environment->cp->returns['get_foot'] = array($html);
        $this->environment->db->results = array(array(), array());
        $this->assertTrue($version->check());
        $this->assertCount(3, $this->environment->cp->calls);
        $this->assertSame(array(), $this->environment->db->results);
    }

    /**
     * Encode each byte of a URL as a numeric HTML entity.
     *
     * @return void
     */
    public function testSpiderSafeAndPublicKey()
    {
        $version = (new \ReflectionClass(VersionSubject::class))->newInstanceWithoutConstructor();
        $this->assertSame('', $this->invoke($version, 'spiderSafe', array('')));
        $this->assertSame('&#104;&#116;&#116;&#112;&#115;&#58;&#47;&#47;', $this->invoke($version, 'spiderSafe', array('https://')));
        $key = $this->invoke($version, 'getOpenSSLPublicKey');
        $details = openssl_pkey_get_details($key);
        $this->assertSame(4096, $details['bits']);
        $this->assertSame(OPENSSL_KEYTYPE_RSA, $details['type']);
    }
    /**
     * Construct the real version service when schema verification is request-cached.
     *
     * @return void
     */
    public function testConstructorUsesVerifiedRequestCache()
    {
        StaticCache::put('verifyLicenseTable', false);
        $version = new VersionSubject;
        $this->assertInstanceOf(\Low\Title\FluxCapacitor\FluxCapacitor::class, $version->flux);
        $this->assertSame(array(), $this->environment->db->calls);
    }

}
