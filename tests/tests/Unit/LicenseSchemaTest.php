<?php

namespace LowTitleTests;

use Low\Title\FluxCapacitor\Conduit\StaticCache;

require_once dirname(__DIR__, 2) . '/Support/BaseSubjects.php';
require_once dirname(__DIR__, 2) . '/Support/SchemaForge.php';

class LicenseSchemaTest extends TestCase
{
    /**
     * Use live schema metadata independently of the recorder's legacy booleans.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        StaticCache::clear();
        ee()->db->schema = new SchemaBackend;
        ee()->db->schema->fields['eeharbor_licenses'] = array('site_id', 'addon', 'license_key', 'ignore_site');
    }

    /**
     * Restore request-wide verification caches after every schema context.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        StaticCache::clear();
        parent::tearDown();
    }

    /**
     * Create every required license column and index before verifying its metadata.
     *
     * @return void
     */
    public function testMissingTableIsCreatedAndVerified(): void
    {
        ee()->db->data_cache = array('table_names' => array('stale'), 'unrelated' => 'keep');
        $version = new VersionSubject();

        $this->assertFalse($version->verifyLicenseTable());
        $this->assertSame(array('eeharbor_licenses'), ee()->db->schema->tables);
        $this->assertSame(array('site_id', 'addon', 'license_key', 'ignore_site'), ee()->db->schema->fields['eeharbor_licenses']);
        $this->assertSame('keep', ee()->db->data_cache['unrelated']);
        $this->assertSame(array(), ee()->db->data_cache['table_names']);
        $this->assertSame(array('exp_eeharbor_licenses'), ee()->dbforge->db->data_cache['table_names']);
        $this->assertSame(array('site_id', 'addon', 'license_key', 'ignore_site'), ee()->db->data_cache['field_names']['eeharbor_licenses']);
        $fields = array(
            'site_id' => array('type' => 'int(11)', 'unsigned' => true, 'null' => false, 'default' => '0'),
            'addon' => array('type' => 'varchar(30)', 'null' => false, 'default' => ''),
            'license_key' => array('type' => 'varchar(50)', 'null' => false, 'default' => ''),
            'ignore_site' => array('type' => 'int(1)', 'unsigned' => true, 'null' => false, 'default' => '0'),
        );
        $this->assertSame(array(array('add_field', $fields), array('add_key', 'license_key', true), array('add_key', array('site_id', 'addon'), false), array('create_table', 'eeharbor_licenses')), ee()->dbforge->calls);
        $this->assertSame(array(array('table_exists', 'eeharbor_licenses'), array('field_exists', 'ignore_site', 'eeharbor_licenses'), array('query', "SHOW KEYS FROM exp_eeharbor_licenses WHERE Key_name = 'PRIMARY'")), ee()->db->calls);
        $this->assertSame(1, ee()->load->forgeCalls);
    }

    /**
     * Add only the missing legacy column and preserve unrelated metadata.
     *
     * @return void
     */
    public function testLegacyIgnoreColumnIsAdded(): void
    {
        ee()->db->schema->tables = array('eeharbor_licenses');
        ee()->db->schema->fields['eeharbor_licenses'] = array('site_id', 'addon', 'license_key');
        ee()->db->schema->primaryKeys['eeharbor_licenses'] = array(array('Column_name' => 'license_key', 'Non_unique' => '0'));
        ee()->db->data_cache['field_names'] = array('eeharbor_licenses' => array('stale'), 'unrelated' => array('keep'));
        $version = new VersionSubject();

        $this->assertFalse($version->verifyLicenseTable());
        $this->assertSame(array(array('add_column', 'eeharbor_licenses', array('ignore_site' => array('type' => 'int(1)', 'unsigned' => true, 'null' => false, 'default' => '0')))), ee()->dbforge->calls);
        $this->assertSame(array('site_id', 'addon', 'license_key', 'ignore_site'), ee()->db->schema->fields['eeharbor_licenses']);
        $this->assertSame(array('eeharbor_licenses' => array('stale'), 'unrelated' => array('keep')), ee()->db->data_cache['field_names']);
        $this->assertFalse(isset(ee()->dbforge->db->data_cache['field_names']['eeharbor_licenses']));
        $this->assertSame(1, ee()->load->forgeCalls);
        $calls = ee()->db->calls;
        $this->assertFalse($version->verifyLicenseTable());
        $this->assertSame($calls, ee()->db->calls);
        $this->assertCount(1, ee()->dbforge->calls);
    }

    /**
     * Return valid, absent, composite, and malformed primary-key metadata.
     *
     * @return array
     */
    public static function primaryKeys(): array
    {
        return array(
            'valid' => array(array(array('Column_name' => 'license_key', 'Non_unique' => '0')), false),
            'missing' => array(array(), true),
            'composite' => array(array(array('Column_name' => 'site_id', 'Non_unique' => '0'), array('Column_name' => 'addon', 'Non_unique' => '0')), true),
            'wrong-column' => array(array(array('Column_name' => 'site_id', 'Non_unique' => '0')), true),
            'non-unique' => array(array(array('Column_name' => 'license_key', 'Non_unique' => '1')), true),
            'null-column' => array(array(array('Column_name' => null, 'Non_unique' => '0')), true),
            'integer-unique' => array(array(array('Column_name' => 'license_key', 'Non_unique' => 0)), true),
            'null-unique' => array(array(array('Column_name' => 'license_key', 'Non_unique' => null)), true),
        );
    }

    /**
     * Preserve the verified corruption result without repeating schema work.
     *
     * @dataProvider primaryKeys
     * @param array $rows
     * @param bool $corrupt
     * @return void
     */
    public function testPrimaryKeyVerificationAndCache(array $rows, $corrupt): void
    {
        ee()->db->schema->tables = array('eeharbor_licenses');
        ee()->db->schema->primaryKeys['eeharbor_licenses'] = $rows;
        $version = new VersionSubject();
        $calls = ee()->db->calls;

        $this->assertSame($corrupt, $version->verifyLicenseTable());
        ee()->db->schema->primaryKeys['eeharbor_licenses'] = array();
        $this->assertSame($corrupt, (new VersionSubject())->verifyLicenseTable());
        $this->assertSame($calls, ee()->db->calls);
        $this->assertSame(0, ee()->load->forgeCalls);
        $this->assertFalse(ee()->has('dbforge'));
    }

    /**
     * Keep an already installed native schema builder when the loader is reused.
     *
     * @return void
     */
    public function testSchemaLoaderRetainsLoadedBuilder(): void
    {
        ee()->load->dbforge();
        $forge = ee()->dbforge;
        ee()->load->dbforge();

        $this->assertInstanceOf(SchemaForge::class, $forge);
        $this->assertNotSame(ee()->db, $forge->db);
        $this->assertSame($forge, ee()->dbforge);
        $this->assertTrue(ee()->has('dbforge'));
        $this->assertSame(array(), $forge->calls);
        $this->assertSame(2, ee()->load->forgeCalls);
    }

    /**
     * Preserve the facade's loaded-null entry without constructing another builder.
     *
     * @return void
     */
    public function testSchemaLoaderPreservesLoadedNull(): void
    {
        ee()->set('dbforge', null);
        ee()->load->dbforge();

        $this->assertTrue(ee()->has('dbforge'));
        $this->assertNull(ee()->dbforge);
        $this->assertSame(1, ee()->load->forgeCalls);
    }

    /**
     * Match native metadata caching and schema-builder invalidation boundaries.
     *
     * @return void
     */
    public function testDatabaseMetadataCacheAndColumnInvalidation(): void
    {
        ee()->db->schema->tables = array('eeharbor_licenses');
        ee()->db->schema->fields['eeharbor_licenses'] = array('license_key');
        ee()->db->data_cache['table_names'] = array('exp_stale');
        $this->assertFalse(ee()->db->table_exists('eeharbor_licenses'));
        unset(ee()->db->data_cache['table_names']);
        $this->assertTrue(ee()->db->table_exists('eeharbor_licenses'));
        $this->assertSame(array('exp_eeharbor_licenses'), ee()->db->data_cache['table_names']);
        $this->assertFalse(ee()->db->field_exists('ignore_site', 'eeharbor_licenses'));
        $this->assertSame(array('license_key'), ee()->db->data_cache['field_names']['eeharbor_licenses']);
        ee()->load->dbforge();
        ee()->dbforge->db->data_cache['field_names']['eeharbor_licenses'] = array('license_key');
        ee()->dbforge->add_column('eeharbor_licenses', array('ignore_site' => array('type' => 'int(1)')));
        $this->assertFalse(isset(ee()->dbforge->db->data_cache['field_names']['eeharbor_licenses']));
        $this->assertFalse(ee()->db->field_exists('ignore_site', 'eeharbor_licenses'));
        unset(ee()->db->data_cache['field_names']['eeharbor_licenses']);
        $this->assertTrue(ee()->db->field_exists('ignore_site', 'eeharbor_licenses'));
        $this->assertSame(array('license_key', 'ignore_site'), ee()->db->data_cache['field_names']['eeharbor_licenses']);
    }

    /**
     * Return native cache positions, including its falsey zero-index lookup.
     *
     * @return array
     */
    public static function tableCaches(): array
    {
        return array(
            'absent' => array(array('exp_other'), array('exp_other', 'exp_records')),
            'first' => array(array('exp_records'), array('exp_records', 'exp_records')),
            'later' => array(array('exp_other', 'exp_records'), array('exp_other', 'exp_records')),
        );
    }

    /**
     * Match native forge cache behavior for explicitly configured query defaults.
     *
     * @dataProvider tableCaches
     * @param array $cache
     * @param array $expected
     * @return void
     */
    public function testForgeUsesNativeTableCacheLookup(array $cache, array $expected): void
    {
        ee()->db->initialDataCache = array('table_names' => $cache);
        ee()->load->dbforge();
        $forge = ee()->dbforge;
        $this->assertSame($cache, $forge->db->data_cache['table_names']);
        $forge->add_field(array('id' => array('type' => 'int(11)')));
        $forge->add_key('id', true);
        $this->assertTrue($forge->create_table('records'));
        $this->assertSame($expected, $forge->db->data_cache['table_names']);
        $this->assertSame(array('records'), ee()->db->schema->tables);
        $this->assertFalse(isset(ee()->db->data_cache['table_names']));
        $this->assertSame(array('table_names' => $cache), ee()->db->initialDataCache);
    }

    /**
     * Share live schema changes without sharing query-builder metadata caches.
     *
     * @return void
     */
    public function testExistingQueryObservesNewSchema(): void
    {
        $query = ee('db');
        ee()->load->dbforge();
        ee()->dbforge->add_field(array('id' => array('type' => 'int(11)')));
        ee()->dbforge->add_key('id', true);
        $this->assertTrue(ee()->dbforge->create_table('records'));

        $this->assertTrue($query->table_exists('records'));
        $this->assertTrue($query->field_exists('id', 'records'));
        $this->assertSame(array('exp_records'), $query->data_cache['table_names']);
        $query->data_cache['table_names'] = array('exp_stale');
        $this->assertFalse($query->table_exists('records'));
        $this->assertFalse(isset(ee()->db->data_cache['table_names']));
        $this->assertSame(array('exp_records'), ee()->dbforge->db->data_cache['table_names']);
        unset($query->data_cache['table_names']);
        $this->assertTrue($query->table_exists('records'));
    }
}
