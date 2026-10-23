<?php

namespace LowTitleTests;

use Low\Title\FluxCapacitor\Conduit\StaticCache;

class ConfiguredQueryTest extends TestCase
{
    /**
     * Start each configured-query context with an independent request cache.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        StaticCache::clear();
    }

    /**
     * Restore the request cache before returning the caller's EE environment.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        StaticCache::clear();
        parent::tearDown();
    }

    /**
     * Keep configured initial metadata separate from facade and per-query caches.
     *
     * @return void
     */
    public function testConfiguredDefaultsSurviveFacadeRemovalAndReplacement()
    {
        $backend = $this->environment->db;
        $initial = array('table_names' => array('exp_initial'), 'unrelated' => 'keep');
        $backend->initialDataCache = $initial;
        $backend->data_cache = array('table_names' => array('exp_facade'));
        $first = ee('db');
        $this->assertSame($initial, $first->data_cache);
        unset($first->data_cache['table_names']);

        $this->environment->remove('db');
        $second = ee('db');
        $replacement = new Database;
        $replacement->initialDataCache = array('replacement' => true);
        $this->environment->set('db', $replacement);
        $third = ee('db');

        $this->assertNotSame($first, $second);
        $this->assertNotSame($second, $third);
        $this->assertSame($initial, $second->data_cache);
        $this->assertSame($initial, $third->data_cache);
        $this->assertSame(array('unrelated' => 'keep'), $first->data_cache);
        $this->assertSame($initial, $backend->initialDataCache);
        $this->assertSame(array('table_names' => array('exp_facade')), $backend->data_cache);
        $this->assertSame($replacement, $this->environment->db);
        $this->assertSame(array(), $replacement->data_cache);
    }

    /**
     * Return modern implementations that request independent database builders.
     *
     * @return array
     */
    public static function bridges()
    {
        return array(array('EE3'), array('EE4'), array('EE5'), array('EE6'));
    }

    /**
     * Clear configured stale table metadata before settings reads and writes.
     *
     * @dataProvider bridges
     * @param string $version
     * @return void
     */
    public function testSettingsRefreshConfiguredMetadata($version)
    {
        $backend = $this->environment->db;
        $initial = array('table_names' => array('exp_stale'), 'unrelated' => 'keep');
        $backend->initialDataCache = $initial;
        $backend->data_cache = array('table_names' => array('exp_facade'));
        $backend->tableExists = true;
        $backend->count = 1;
        $backend->results = array(array(array('custom' => 'before')), array());
        $this->environment->config->config['site_id'] = 19;
        $factory = $this->environment->services['db'];
        $queries = array();
        $this->environment->services['db'] = function () use ($factory, &$queries) {
            $query = $factory();
            $queries[] = $query;

            return $query;
        };
        $class = 'Low\\Title\\FluxCapacitor\\Bridge\\' . $version;
        $bridge = new $class(array('module' => 'low_title', 'module_name' => 'Low Title'));
        $this->assertSame('before', $bridge->getConfig('custom'));
        $bridge->setConfig('custom', 'after');

        $this->assertSame('after', $bridge->getConfig('custom'));
        $this->assertCount(2, $queries);
        foreach ($queries as $query) {
            $this->assertNotSame($backend, $query);
            $this->assertSame(array(array('unrelated' => 'keep')), $query->tableCheckCaches);
            $this->assertSame(array('unrelated' => 'keep'), $query->data_cache);
        }
        $this->assertContains(array('get_where', 'low_title_settings', array('low_title_settings.site_id' => 19)), $queries[0]->calls);
        $this->assertSame(array(
            array('table_exists', 'low_title_settings'),
            array('where', 'site_id', 19),
            array('count_all_results', 'low_title_settings'),
            array('where', 'site_id', 19),
            array('update', 'low_title_settings', array('site_id' => 19, 'custom' => 'after')),
        ), $queries[1]->calls);
        $this->assertSame($initial, ee('db')->data_cache);
        $this->assertSame($initial, $backend->initialDataCache);
        $this->assertSame(array('table_names' => array('exp_facade')), $backend->data_cache);
        $this->assertSame(array(), $backend->calls);
    }
}
