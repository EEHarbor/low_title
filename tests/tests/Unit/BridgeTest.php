<?php

namespace LowTitleTests;

use Low\Title\FluxCapacitor\Conduit\StaticCache;

class BridgeTest extends TestCase
{
    /**
     * Create concrete EE collaborators with explicit service contracts.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        StaticCache::clear();
        $this->environment->input = new Input;
        $this->environment->uri = new Uri;
        $this->environment->view = (object) array('cp_page_title' => 'Low Title');
        foreach (array('cp', 'api', 'legacy_api', 'functions', 'addons_model', 'session', 'cache') as $name) {
            $this->environment->$name = new Recorder;
        }
        $factory = new UrlFactory;
        $this->environment->services['CP/URL'] = function ($path = null) use ($factory) { return $path === null ? $factory : $factory->make($path); };
    }

    /**
     * Restore global request cache after each bridge context.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        StaticCache::clear();
        parent::tearDown();
    }

    /**
     * Return each independently shipped version of the bridge implementation.
     *
     * @return array
     */
    public static function bridges()
    {
        return array(array('EE2'), array('EE3'), array('EE4'), array('EE5'), array('EE6'));
    }

    /**
     * Construct an archived bridge without replacing shipped methods.
     *
     * @param string $version
     * @return object
     */
    private function bridge($version)
    {
        $class = 'Low\\Title\\FluxCapacitor\\Bridge\\' . $version;

        return new $class(array('module' => 'low_title', 'module_name' => 'Low Title'));
    }

    /**
     * Preserve version comparison, pagination offset, and cache path contracts.
     *
     * @dataProvider bridges
     * @param string $version
     * @return void
     */
    public function testPureBridgeHelpers($version)
    {
        $bridge = $this->bridge($version);
        $this->assertSame('7', $bridge->getEEVersion());
        $this->assertSame(APP_VER, $bridge->getEEVersion(false));
        $this->assertTrue($bridge->ver_lt('8'));
        $this->assertTrue($bridge->ver_gt('6'));
        $this->assertTrue($bridge->ver_lte('7'));
        $this->assertTrue($bridge->ver_gte('7'));
        foreach (array('is_ee2', 'is_ee3', 'is_ee4', 'is_ee5') as $method) {
            $this->assertFalse($bridge->$method());
        }
        $this->assertSame(0, $bridge->getStartNum(array('current_page' => 1, 'per_page' => 25)));
        $this->assertSame(50, $bridge->getStartNum(array('current_page' => 3, 'per_page' => 25)));
        $this->environment->config->config['cache_path'] = '/configured/cache/';
        $this->assertSame('/configured/cache/', $bridge->getCachePath());
        unset($this->environment->config->config['cache_path']);
        $this->assertSame($version === 'EE2' ? APPPATH . 'cache/' : PATH_CACHE, $bridge->getCachePath());
        $this->environment->config->config['theme_folder_url'] = '/themes/';
        $this->assertSame('/themes/' . ($version === 'EE2' ? 'third_party/' : 'user/') . 'low_title/', $bridge->getAddonThemesDir());
        $this->assertSame('https://example.test/a/b', $bridge->reduce_double_slashes('https://example.test//a///b'));
    }

    /**
     * Return current page query vectors for each bridge implementation.
     *
     * @return array
     */
    public static function pages()
    {
        $cases = array();
        foreach (self::bridges() as $bridge) {
            foreach (array(array(), array('per_page' => '50'), array('page' => '3'), array('per_page' => '0', 'page' => '4')) as $input) {
                $cases[] = array($bridge[0], $input);
            }
        }

        return $cases;
    }

    /**
     * Respect per_page precedence and the legacy offset-to-page conversion.
     *
     * @dataProvider pages
     * @param string $version
     * @param array $input
     * @return void
     */
    public function testCurrentPage($version, array $input)
    {
        $this->environment->input->getValues = $input;
        $expected = !empty($input['per_page']) ? ($version === 'EE2' ? 3 : '50') : (isset($input['page']) ? ($version === 'EE2' ? (int) $input['page'] : $input['page']) : 1);
        $this->assertEquals($expected, $this->bridge($version)->getCurrentPage(array('per_page' => 25)));
    }

    /**
     * Load metadata and merged persisted settings without crossing site boundaries.
     *
     * @dataProvider bridges
     * @param string $version
     * @return void
     */
    public function testSettingsMergeAndCache($version)
    {
        $this->environment->db->tableExists = true;
        $this->environment->db->results = array(array(array('site_id' => 1, 'custom' => 'database', 'version' => 'wrong')), array(array('settings' => base64_encode(serialize(array('custom' => 'fieldtype', 'field-only' => 'kept'))))));
        $this->environment->db->data_cache = array('table_names' => array('stale'));
        $bridge = $this->bridge($version);
        $settings = $bridge->getSettings(true);
        $this->assertSame('database', $settings['custom']);
        $this->assertSame('kept', $settings['field-only']);
        $this->assertSame(json_decode(file_get_contents(PATH_THIRD . 'low_title/addon.json'), true)['version'], $settings['version']);
        $this->assertSame($settings, (array) $bridge->getSettings());
        $this->assertSame('database', $bridge->getConfig('custom'));
    }

    /**
     * Return empty field settings vectors including valid serialized false.
     *
     * @return array
     */
    public static function emptySettings()
    {
        $cases = array();
        foreach (self::bridges() as $bridge) {
            foreach (array(array(), array(array('settings' => base64_encode(serialize(false))))) as $rows) {
                $cases[] = array($bridge[0], $rows);
            }
        }

        return $cases;
    }

    /**
     * Fall back to addon.setup metadata when persisted settings are absent or false.
     *
     * @dataProvider emptySettings
     * @param string $version
     * @param array $rows
     * @return void
     */
    public function testEmptySettings($version, $rows)
    {
        $this->environment->db->results = array($rows);
        $this->assertSame(require PATH_THIRD . 'low_title/addon.setup.php', $this->bridge($version)->getSettings(true));
    }

    /**
     * Return persistence existence cases for each modern bridge.
     *
     * @return array
     */
    public static function settingsWrites()
    {
        $cases = array();
        foreach (array('EE2', 'EE3', 'EE4', 'EE5', 'EE6') as $version) {
            foreach (array(false, true) as $exists) {
                foreach (array(0, 1) as $count) {
                    $cases[] = array($version, $exists, $count);
                }
            }
        }

        return $cases;
    }

    /**
     * Insert or update only the current site's persisted setting and refresh cache.
     *
     * @dataProvider settingsWrites
     * @param string $version
     * @param bool $exists
     * @param int $count
     * @return void
     */
    public function testSettingsWrites($version, $exists, $count)
    {
        StaticCache::put('app_settings', array('old' => 'value'));
        $this->environment->db->tableExists = $exists;
        $this->environment->db->count = $count;
        $this->environment->db->data_cache = array('table_names' => array('stale'));
        $bridge = $this->bridge($version);
        $bridge->setConfig('custom', 'new');
        $this->assertSame('new', $bridge->getConfig('custom'));
        $writes = array_values(array_filter($this->environment->db->journal->calls, function ($call) { return in_array($call[0], array('insert', 'update'), true); }));
        $this->assertSame($exists ? array(array($count ? 'update' : 'insert', 'low_title_settings', array('site_id' => 1, 'custom' => 'new'))) : array(), $writes);
        $this->assertArrayHasKey('table_names', $this->environment->db->data_cache);
        $this->assertSame(array(), $this->environment->db->calls, 'Settings must use a separate query builder');
        if ($exists) {
            $this->assertContains(array('where', 'site_id', 1), $this->environment->db->journal->calls);
        }
    }

    /**
     * Return modern control-panel URL routing cases.
     *
     * @return array
     */
    public static function urls()
    {
        $cases = array();
        foreach (array('EE3', 'EE4', 'EE5', 'EE6') as $version) {
            foreach (array(array('listing', '', array(), 'publish'), array('publish', 'create', array('channel_id' => 3, 'x' => 1), 'publish/create/3'), array('publish', 'edit', array('entry_id' => 12, 'x' => 1), 'publish/edit/entry/12'), array('publish', 'edit', array(), 'publish/edit'), array('members', 'roles', array(), 'members/roles')) as $route) {
                $cases[] = array_merge(array($version), $route);
            }
        }

        return $cases;
    }

    /**
     * Route CP links while moving entry and channel identifiers into path segments.
     *
     * @dataProvider urls
     * @param string $version
     * @param string $path
     * @param string $mode
     * @param array $variables
     * @param string $expected
     * @return void
     */
    public function testControlPanelUrls($version, $path, $mode, $variables, $expected)
    {
        $bridge = $this->bridge($version);
        $url = $bridge->cpURL($path, $mode, $variables);
        $this->assertSame($expected, $url->path);
        $this->assertSame(isset($variables['x']) ? array('x' => 1) : array(), $url->variables);
        $this->assertSame('addons/settings/low_title/index', $bridge->moduleURL()->path);
        $this->assertSame('addons/settings/low_title', $bridge->getBaseURL('/')->path);
        $this->assertSame('addons/settings/low_title/entry?x=1', $bridge->getBaseURL('entry', '?x=1')->path);
        $this->assertSame('addons/settings/low_title', $bridge->getBaseURL()->path);
    }

    /**
     * Return complete and index URI contexts for modern and legacy bridges.
     *
     * @dataProvider bridges
     * @param string $version
     * @return void
     */
    public function testCurrentUrlInfo($version)
    {
        $originalGet = $_GET;
        $originalServer = $_SERVER;
        try {
            $_SERVER['QUERY_STRING'] = 'cp/addons/settings/low_title';
            $_GET = array('module' => 'low_title');
            $this->environment->uri->path = 'cp/addons/settings/low_title';
            $bridge = $this->bridge($version);
            $this->assertSame('index', $bridge->getCurrentUrlInfo('method'));
            $this->assertSame('low_title', $bridge->getCurrentUrlInfo('module'));
            $this->assertTrue($bridge->getCurrentUrlInfo('cp'));
            $this->assertIsArray($bridge->getCurrentUrlInfo('unknown'));
            $_GET['method'] = 'settings';
            $this->environment->uri->path .= '/settings';
            $this->assertSame('settings', $bridge->getCurrentUrlInfo('method'));
            $this->assertSame('cp', $bridge->getCurrentUrlInfo()['segments'][0]);
        } finally {
            $_GET = $originalGet;
            $_SERVER = $originalServer;
        }
    }
    /**
     * Delegate EE APIs, parser services, security, and footer operations faithfully.
     *
     * @dataProvider bridges
     * @param string $version
     * @return void
     */
    public function testDelegatedServices($version)
    {
        $bridge = $this->bridge($version);
        $api = $version === 'EE2' ? 'api' : 'legacy_api';
        $this->environment->$api->returns['instantiate'] = null;
        $bridge->instantiate('channel_entries');
        $this->assertSame(array(array('instantiate', 'channel_entries')), $this->environment->$api->calls);
        $this->environment->cp->returns = array('get_foot' => array('existing'), 'add_to_foot' => null);
        $this->environment->cp->footer_item = array('existing');
        $this->assertSame(array('existing'), $bridge->getFooter());
        $bridge->javascript_to_page('window.test = true;');
        $this->assertContains(array('add_to_foot', '<script type="text/javascript">window.test = true;</script>'), $this->environment->cp->calls);
        $this->environment->addons_model->returns['module_installed'] = true;
        $this->environment->db->count = 1;
        $this->assertTrue($bridge->module_installed('Low_title'));
        $this->environment->security = new Recorder;
        $this->environment->security->returns['xss_clean'] = 'clean';
        $security = new Recorder;
        $security->returns['clean'] = 'clean';
        $this->environment->services['Security/XSS'] = $security;
        $this->assertSame('clean', $bridge->xss_clean('<script>bad</script>'));
        $this->environment->functions->returns['assign_variables'] = array('var' => array());
        $this->environment->functions->returns['assign_parameters'] = array('x' => '1');
        $parser = new Recorder;
        $parser->returns = array('extractVariables' => array('var' => array()), 'parseTagParameters' => array('x' => '1'));
        $this->environment->services['Variables/Parser'] = $parser;
        $this->assertSame(array('var' => array()), $bridge->assign_variables('{var}'));
        $this->assertSame(array('x' => '1'), $bridge->assign_parameters('x="1"', array('default' => 'v')));
        $formatter = new Recorder;
        $formatter->returns = array('make' => $formatter, 'urlSlug' => $formatter, 'compile' => 'hello-world', 'url' => 'https://example.test');
        $this->environment->services['Format'] = $formatter;
        $this->assertSame('hello-world', $bridge->url_title('  HELLO WORLD  '));
        if ($version !== 'EE2') {
            $this->assertSame('https://example.test', $bridge->prep_url('example.test'));
        }
        $bridge->overwriteEEClass('replaced', 'value');
        $this->assertSame('value', $this->environment->replaced);
        $bridge->removeEEClass('replaced');
        $this->assertSame($version === 'EE2' ? '' : false, isset($this->environment->replaced) ? $this->environment->replaced : false);
    }

    /**
     * Return existing service values, including loaded null, for every bridge.
     *
     * @return array
     */
    public static function loadedServices()
    {
        $cases = array();
        foreach (self::bridges() as $bridge) {
            foreach (array(null, 'original') as $value) {
                $cases[] = array($bridge[0], $value);
            }
        }

        return $cases;
    }

    /**
     * Replace an already loaded EE service without violating the facade contract.
     *
     * @dataProvider loadedServices
     * @param string $version
     * @param mixed $value
     * @return void
     */
    public function testReplaceLoadedService($version, $value)
    {
        $this->environment->set('replaced', $value);
        $this->bridge($version)->overwriteEEClass('replaced', 'replacement');
        $this->assertSame('replacement', $this->environment->replaced);
    }

    /**
     * Return persistent/transient cache operations for every bridge.
     *
     * @return array
     */
    public static function cacheModes()
    {
        $cases = array();
        foreach (self::bridges() as $bridge) {
            foreach (array('get', 'set', 'delete', 'clear', 'unknown') as $mode) {
                foreach (array(false, true) as $persistent) {
                    $cases[] = array($bridge[0], $mode, $persistent);
                }
            }
        }

        return $cases;
    }

    /**
     * Route cache operations to EE's persistent or request-local storage.
     *
     * @dataProvider cacheModes
     * @param string $version
     * @param string $mode
     * @param bool $persistent
     * @return void
     */
    public function testCacheDelegation($version, $mode, $persistent)
    {
        $this->environment->cache = new StatefulCache;
        $this->environment->cache->store = array('/low_title/key' => 'cached');
        $this->environment->session->returns = array('cache' => 'cached', 'set_cache' => true);
        $this->environment->session->cache = array('low_title' => array('key' => 'cached', 'other' => 'retained'));
        $bridge = $this->bridge($version);
        $result = $bridge->cache($mode, 'key', 'value', $persistent);
        if ($mode === 'unknown') {
            $this->assertFalse($result);
            return;
        }
        if ($version === 'EE2' && !$persistent) {
            $this->assertSame($mode === 'get' ? 'cached' : ($mode === 'set' ? 'value' : null), $result);
            $this->assertSame('retained', $this->environment->session->cache['low_title']['other']);
            return;
        }
        if ($version === 'EE2' && in_array($mode, array('delete', 'clear'), true)) {
            $this->assertNull($result);
            $this->assertArrayNotHasKey('key', $this->environment->session->cache['low_title']);
            $this->assertFalse($bridge->cache('get', 'key', false, true), 'A persistent deletion must remove the persistent value');
            return;
        }
        $this->assertSame($mode === 'get' ? 'cached' : true, $result);
        $recorder = $persistent || in_array($mode, array('delete', 'clear'), true) ? $this->environment->cache : $this->environment->session;
        $method = array('get' => $persistent ? 'get' : 'cache', 'set' => $persistent ? 'save' : 'set_cache', 'delete' => 'delete', 'clear' => 'delete')[$mode];
        $this->assertSame($method, $recorder->calls[0][0]);
    }

    /**
     * Return alert severity variants for all bridge versions.
     *
     * @return array
     */
    public static function alerts()
    {
        $cases = array();
        foreach (self::bridges() as $bridge) {
            foreach (array('message_error', 'message_failure', 'message_notice', 'message_success') as $type) {
                $cases[] = array($bridge[0], $type);
            }
        }

        return $cases;
    }

    /**
     * Preserve alert severity and optional modifiers when flashing messages.
     *
     * @dataProvider alerts
     * @param string $version
     * @param string $type
     * @return void
     */
    public function testFlashMessages($version, $type)
    {
        $alert = new Alert;
        foreach (array('make', 'makeInline', 'asIssue', 'asWarning', 'asSuccess', 'withTitle', 'addToBody', 'canClose', 'defer') as $method) {
            $alert->returns[$method] = $alert;
        }
        $this->environment->services['CP/Alert'] = $alert;
        $this->environment->session->returns['set_flashdata'] = null;
        $this->bridge($version)->flashData($type, 'Title', 'Body', array('missingMethod', 'cannotClose'));
        if ($version === 'EE2') {
            $this->assertSame(array(array('set_flashdata', $type, 'Title Body')), $this->environment->session->calls);
            return;
        }
        $severity = in_array($type, array('message_error', 'message_failure'), true) ? 'asIssue' : ($type === 'message_notice' ? 'asWarning' : 'asSuccess');
        $this->assertSame(array(array($version === 'EE3' ? 'make' : 'makeInline', 'Title'), array($severity), array('withTitle', 'Title'), array('addToBody', 'Body'), array('canClose'), array('cannotClose'), array('defer')), $alert->calls);
    }

    /**
     * Return view and pagination contexts for each modern bridge.
     *
     * @return array
     */
    public static function modernViews()
    {
        return array(array('EE3', false), array('EE3', '<inline>'), array('EE4', false), array('EE4', '<inline>'), array('EE5', false), array('EE5', '<inline>'), array('EE6', false), array('EE6', '<inline>'));
    }

    /**
     * Return the heading, breadcrumb, rendered content, and template variables.
     *
     * @dataProvider modernViews
     * @param string $version
     * @param string|false $inline
     * @return void
     */
    public function testViewAndPagination($version, $inline)
    {
        $view = new ViewFactory;
        $this->environment->services['View'] = $view;
        $bridge = $this->bridge($version);
        $result = $bridge->view('license', array('license_key' => 'KEY'), true, $inline);
        $this->assertSame(array('heading' => 'Low Title', 'breadcrumb' => array('/cp/addons/settings/low_title/' => 'Low Title'), 'body' => 'rendered:' . ($inline ?: 'low_title:license')), $result);
        $this->assertSame('7', $view->variables['ee_ver']);
        $this->assertSame('KEY', $view->variables['license_key']);
        $bridge->view('license', array('base_url' => '/given', 'cp_page_title' => 'Given'));
        $this->assertSame('/given', $view->variables['base_url']);
        $this->assertSame('Given', $view->variables['cp_page_title']);
        $pagination = new Recorder;
        foreach (array('perPage', 'currentPage', 'displayPageLinks', 'queryStringVariable') as $method) {
            $pagination->returns[$method] = $pagination;
        }
        $pagination->returns['render'] = '<pagination>';
        $paginationTotals = array();
        $this->environment->services['CP/Pagination'] = function ($total) use ($pagination, &$paginationTotals) {
            $paginationTotals[] = $total;

            return $pagination;
        };
        $this->assertSame('<pagination>', $bridge->pagination(array('total_rows' => 100, 'per_page' => 10, 'current_page' => 2, 'base_url' => '/entries', 'page_links' => 5, 'page_query_string' => 'page')));
        $this->assertSame(array(array('perPage', 10), array('currentPage', 2), array('displayPageLinks', 5), array('queryStringVariable', 'page'), array('render', '/entries')), $pagination->calls);
        $pagination->calls = array();
        $bridge->pagination(array('total_rows' => 0, 'per_page' => 10, 'current_page' => 1, 'base_url' => '/entries'));
        $this->assertCount(3, $pagination->calls);
        $this->assertSame(array(100, 0), $paginationTotals);
    }

    /**
     * Build modern navigation with internal links, external links, and action buttons.
     *
     * @dataProvider modernViews
     * @param string $version
     * @param string|false $mapped
     * @return void
     */
    public function testNavigation($version, $mapped)
    {
        $sidebar = new Sidebar;
        $factory = new Recorder;
        $factory->returns['make'] = $sidebar;
        $this->environment->services['CP/Sidebar'] = $factory;
        $this->environment->cp->returns['set_breadcrumb'] = null;
        $this->environment->uri->path = 'cp/addons/settings/low_title/' . ($mapped ? 'save' : 'index');
        $this->bridge($version)->getNav(array('/' => 'Overview', 'settings' => 'Settings', 'https://example.test' => 'External'), array('settings' => array('create' => 'New')), $mapped ? array('save' => 'settings') : array());
        $this->assertCount(3, $sidebar->items);
        $this->assertSame('https://example.test', $sidebar->items[2]->url);
        $this->assertSame('addons/settings/low_title/create', $sidebar->items[1]->buttons[0][1]->path);
        $this->assertTrue($sidebar->items[$mapped ? 1 : 0]->active);
        $this->assertSame($mapped ? 'Settings' : 'Overview', $this->environment->view->cp_page_title);
    }

    /**
     * Read the legacy key/value structure settings shape through each bridge.
     *
     * @dataProvider bridges
     * @param string $version
     * @return void
     */
    public function testLegacyStructureSettings($version)
    {
        $this->environment->db->tableExists = true;
        $this->environment->db->results = array(array(array('var' => 'custom', 'var_value' => 'kept')), array());
        $class = 'Low\\Title\\FluxCapacitor\\Bridge\\' . $version;
        $bridge = new $class(array('module' => 'structure', 'module_name' => 'Structure'));
        $this->assertSame('kept', $bridge->getConfig('custom'));
    }

    /**
     * Exercise legacy URL routes, sessions, navigation, views, and pagination.
     *
     * @return void
     */
    public function testLegacyControlPanelContracts()
    {
        $bridge = $this->bridge('EE2');
        $this->environment->session->returns['userdata'] = 'fingerprint';
        $this->environment->config->config['cp_session_type'] = 's';
        $this->assertStringContainsString('&amp;S=fingerprint', $bridge->getBaseURL('entry'));
        $this->environment->config->config['cp_session_type'] = 'cs';
        $this->assertStringContainsString('&amp;S=fingerprint', $bridge->getBaseURL('/'));
        $this->assertStringNotContainsString('S=fingerprint', $bridge->getBaseURL('', '&amp;S=existing&amp;D=cp'));
        $this->environment->config->config['cp_session_type'] = false;
        $this->assertStringContainsString('&amp;method=edit&amp;entry_id=12', $bridge->moduleURL('edit', array('entry_id' => 12)));
        $this->assertStringContainsString('&amp;method=index', $bridge->moduleURL('index', null));
        $this->assertSame(BASE . '&amp;D=cp&amp;C=content_edit&amp;channel_id=3', $bridge->cpURL('listing', 'ignored', array('filter_by_channel' => 3)));
        $this->assertSame(BASE . '&amp;D=cp&amp;C=content_publish&amp;M=entry_form', $bridge->cpURL('publish', 'create'));
        $this->assertSame(BASE . '&amp;D=cp&amp;C=members&amp;M=member_group_manager', $bridge->cpURL('members', 'groups'));
        $this->assertSame(BASE . '&amp;D=cp&amp;C=admin_content&amp;M=channel_add', $bridge->cpURL('channels', 'create'));
        $this->assertSame(BASE . '&amp;D=cp&amp;C=addons_modules', $bridge->cpURL('addons'));
        $this->assertSame(BASE . '&amp;D=cp&amp;C=unknown&amp;M=custom', $bridge->cpURL('unknown', 'custom'));
        $this->environment->cp->returns['set_right_nav'] = null;
        $bridge->getNav(array('index' => 'Overview', 'https://example.test' => 'External'));
        $this->assertSame('https://example.test', $this->environment->cp->calls[0][1]['External']);
        $this->assertSame('rendered:license', $bridge->view('license', array('key' => 'KEY'), true));
        $this->assertSame(array(array('license', array('key' => 'KEY', 'ee_ver' => '7'), true)), $this->environment->load->views);
        $this->environment->pagination = new Recorder;
        $this->environment->pagination->returns = array('initialize' => null, 'create_links' => '<pagination>');
        $this->assertSame('<pagination>', $bridge->pagination(array('current_page' => 3)));
        $this->assertSame(array('initialize', array('current_page' => 3, 'cur_page' => 3)), $this->environment->pagination->calls[0]);
        $bridge->pagination(array());
        $this->assertSame(array('initialize', array()), $this->environment->pagination->calls[2]);
        $this->environment->db->count = 0;
        $this->assertFalse($bridge->module_installed('Absent'));
        $this->environment->session->cache = array();
        $this->assertFalse($bridge->cache('get', 'absent', false, false));
        $bridge->cache('clear', false, false, false);
        $this->assertArrayNotHasKey('low_title', $this->environment->session->cache);
    }

}
