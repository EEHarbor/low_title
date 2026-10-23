<?php

namespace LowTitleTests;

use Low\Title\FluxCapacitor\Conduit\StaticCache;

require_once dirname(__DIR__, 2) . '/Support/BaseSubjects.php';

class McpTest extends TestCase
{
    /**
     * Configure request-local control-panel collaborators.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        StaticCache::clear();
        $this->environment->input = new Input;
        $this->environment->functions = new Functions;
    }

    /**
     * Clear cached license state after each control-panel request.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        StaticCache::clear();
        parent::tearDown();
    }

    /**
     * Create an MCP behavior subject with explicit bridge outputs.
     *
     * @return McpSubject
     */
    private function mcp()
    {
        $mcp = (new \ReflectionClass(McpSubject::class))->newInstanceWithoutConstructor();
        $mcp->flux = new Recorder;
        $mcp->flux->returns = array('moduleURL' => '/license', 'view' => '<license>', 'flashData' => null, 'pagination' => '<pagination>');

        return $mcp;
    }

    /**
     * Return licensed, ignored, and absent license storage contexts.
     *
     * @return array
     */
    public static function licenses()
    {
        return array(array(array(), '', false), array(array(array('license_key' => 'KEY', 'ignore_site' => '0')), 'KEY', 0), array(array(array('license_key' => 'KEY', 'ignore_site' => '1')), '', 1));
    }

    /**
     * Render current-site license details and omit ignored-site keys from the form.
     *
     * @dataProvider licenses
     * @param array $rows
     * @param string $key
     * @param int|false $ignored
     * @return void
     */
    public function testLicenseDetails($rows, $key, $ignored)
    {
        $this->environment->db->results = array($rows);
        $mcp = $this->mcp();
        $this->assertSame('<license>', $mcp->license());
        $this->assertSame(array('view', 'license', array('ee_ver' => '7', 'action_url' => '/license', 'license_key' => $key, 'ignore_site' => $ignored), true), $mcp->flux->calls[1]);
        $this->assertSame(array(array('where', 'site_id', 1), array('where', 'addon', 'Low\\Title'), array('get', 'eeharbor_licenses', 1)), $this->environment->db->calls);
    }

    /**
     * Redirect the default MCP route to its license screen.
     *
     * @return void
     */
    public function testIndexRedirects()
    {
        $this->expectException(Redirect::class);
        $this->mcp()->index();
    }

    /**
     * Clone counting queries so pagination does not consume the returned model.
     *
     * @return void
     */
    public function testModelPagination()
    {
        $mcp = $this->mcp();
        $model = new ModelQuery;
        $this->environment->input->getValues['page'] = '3';
        $this->assertSame($model, $mcp->paginateModel($model, '/entries', 10, 7, 'page'));
        $this->assertSame(array(array('limit', 10), array('offset', 20)), $model->calls);
        $this->assertSame(array('pagination' => '<pagination>'), $mcp->data);
        $this->assertContains(array('pagination', array('per_page' => 10, 'current_page' => 3, 'total_rows' => 100, 'base_url' => '/entries', 'page_links' => 7, 'page_query_string' => 'page')), $mcp->flux->calls);
        $this->environment->input->getValues = array();
        $this->assertSame($model, $mcp->paginateModel($model));
        $this->assertSame(array('offset', 0), $model->calls[3]);
    }

    /**
     * Return saved-license inputs and the stored license rows they encounter.
     *
     * @return array
     */
    public static function saves()
    {
        return array(
            'new' => array('KEY-123', false, array(), 0, '', 'message_success'),
            'ignore' => array('', '1', array(), 0, '', 'message_success'),
            'overlong' => array(str_repeat('a', 51), false, array(), 0, '', 'message_error'),
            'other-site' => array('KEY-123', false, array(array('site_id' => 2, 'addon' => 'Low\\Title')), 0, '', 'message_error'),
            'other-addon' => array('KEY-123', false, array(array('site_id' => 1, 'addon' => 'Other\\Addon')), 0, '', 'message_error'),
            'unignore' => array('', false, array(), 1, 'low-title-ignore-site-1', 'message_success'),
            'update' => array('KEY-456', false, array(), 1, 'KEY-123', 'message_success'),
        );
    }

    /**
     * Save licenses with length, duplicate-site, duplicate-add-on, and ignored-site guards.
     *
     * @dataProvider saves
     * @param string $key
     * @param string|false $ignored
     * @param array $existing
     * @param int $count
     * @param string $current
     * @param string $messageType
     * @return void
     */
    public function testSaveLicense($key, $ignored, $existing, $count, $current, $messageType)
    {
        StaticCache::put('verifyLicenseTable', false);
        $this->environment->input->postValues = array('license_key' => $key, 'ignore_site' => $ignored);
        $this->environment->db->count = $count;
        $this->environment->db->results = array($existing);
        if ($existing && $existing[0]['site_id'] !== 1) {
            $this->environment->db->results[] = array(array('site_label' => 'Other Site'));
        }
        if ($count) {
            $this->environment->db->results[] = array(array('license_key' => $current));
        }
        $mcp = $this->mcp();
        try {
            $mcp->save_license();
            $this->fail('Expected the request to redirect');
        } catch (Redirect $redirect) {
            $this->assertSame('/license', $redirect->location);
        }
        $messages = array_values(array_filter($mcp->flux->calls, function ($call) { return $call[0] === 'flashData'; }));
        $this->assertSame($messageType, $messages[0][1]);
        $writes = array_values(array_filter($this->environment->db->calls, function ($call) { return in_array($call[0], array('insert', 'update', 'delete'), true); }));
        if ($messageType === 'message_error') {
            $this->assertSame(array(), $writes);
            return;
        }
        $this->assertCount(1, $writes);
        if ($current && !$key) {
            $this->assertSame(array('delete', 'eeharbor_licenses'), $writes[0]);
            return;
        }
        $this->assertSame($ignored ? 'low-title-ignore-site-1' : $key, $writes[0][2]['license_key']);
    }
}
