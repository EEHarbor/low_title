<?php

namespace LowTitleTests;

use Low\Title\FluxCapacitor\Conduit\StaticCache;

require_once dirname(__DIR__, 2) . '/Support/BaseSubjects.php';

class BaseTest extends TestCase
{
    /**
     * Initialize request inputs and isolate FluxCapacitor configuration.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        StaticCache::clear();
        $this->environment->input = new Input;
        $this->environment->uri = new Uri;
        $this->environment->functions = new Recorder;
        $this->environment->functions->returns['clear_caching'] = null;
        $this->environment->table = new Recorder;
        $this->environment->table->returns['add_row'] = null;
    }

    /**
     * Restore process-global configuration after each base-class test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        StaticCache::clear();
        parent::tearDown();
    }

    /**
     * Build an instance for behavior tests isolated from native constructor defects.
     *
     * @param string $class
     * @param bool $legacy
     * @return object
     */
    private function subject($class, $legacy = false)
    {
        $subject = (new \ReflectionClass(__NAMESPACE__ . '\\' . $class))->newInstanceWithoutConstructor();
        $subject->flux = new Recorder;
        $subject->flux->returns['is_ee2'] = $legacy;
        $subject->flux->returns['moduleURL'] = '/license';

        return $subject;
    }

    /**
     * Return whitespace, fallback, false-like, and literal parameter cases.
     *
     * @return array
     */
    public static function parameters()
    {
        return array(array(false, true, 'fallback'), array('', true, 'fallback'), array('0', true, 'fallback'), array('  hello&nbsp;world&#32; ', true, 'hello world'), array('  literal  ', false, '  literal  '), array('  ', true, 'fallback'), array('1.5', true, '1.5'));
    }

    /**
     * Trim template parameter whitespace and retain configured fallback semantics.
     *
     * @dataProvider parameters
     * @param mixed $value
     * @param bool $trim
     * @param mixed $expected
     * @return void
     */
    public function testModuleParameters($value, $trim, $expected)
    {
        $this->environment->TMPL->tagparams['value'] = $value;
        $this->assertSame($expected, $this->invoke($this->subject('ModuleSubject'), 'getParameter', array('value', 'fallback', $trim)));
    }

    /**
     * Register a new extension using its serialized settings and explicit options.
     *
     * @return void
     */
    public function testExtensionRegistrationAndExistingGuard()
    {
        $extension = $this->subject('ExtensionSubject');
        $extension->settings = array('active' => true);
        $this->environment->db->results = array(array(), array(array('extension_id' => 1)));
        $this->assertTrue($extension->registerExtension('onSave', 'entry_saved', 5, 'n'));
        $this->assertSame(array('insert', 'extensions', array('class' => ExtensionSubject::class, 'method' => 'onSave', 'hook' => 'entry_saved', 'settings' => serialize(array('active' => true)), 'priority' => 5, 'version' => '1.2.3', 'enabled' => 'n')), $this->environment->db->calls[1]);
        $this->assertTrue($extension->registerExtension('onSave', 'entry_saved'));
        $this->assertCount(3, $this->environment->db->calls);
    }

    /**
     * Normalize absent settings and infer a hook name from the method.
     *
     * @return void
     */
    public function testExtensionDefaultsAndAliases()
    {
        $extension = $this->subject('ExtensionSubject');
        $extension->settings = null;
        $this->environment->db->results = array(array());
        $extension->registerExtension('entry_saved');
        $this->assertSame(array(), $extension->settings);
        $this->assertSame('entry_saved', $this->environment->db->calls[1][2]['hook']);
        $this->assertTrue($this->invoke($extension, 'unregisterExtension', array('entry_saved')));
        $this->assertTrue($this->invoke($extension, 'unregisterExtension', array('other', 'entry_deleted')));
        $this->assertTrue($this->invoke($extension, 'update_version'));
        $this->assertTrue($extension->cp_custom_menu(new \stdClass));
    }

    /**
     * Return EE2 and modern extension disable contexts.
     *
     * @return array
     */
    public static function legacyContexts()
    {
        return array(array(false), array(true));
    }

    /**
     * Remove only extension-owned hooks and modern menu references.
     *
     * @dataProvider legacyContexts
     * @param bool $legacy
     * @return void
     */
    public function testExtensionDisable($legacy)
    {
        $this->assertTrue($this->subject('ExtensionSubject', $legacy)->disable_extension());
        $expected = array(array('where', 'class', ExtensionSubject::class), array('delete', 'extensions'));
        if (!$legacy) {
            $expected[] = array('where', 'data', ExtensionSubject::class);
            $expected[] = array('delete', 'menu_items');
        }
        $this->assertSame($expected, $this->environment->db->calls);
    }

    /**
     * Activate every declared hook before storing the extension version.
     *
     * @return void
     */
    public function testExtensionActivationAndUpdateAliases()
    {
        $extension = $this->subject('ExtensionSubject');
        $extension->hooks = array('entry_saved');
        $this->environment->db->results = array(array(), array(array('extension_id' => 1)));
        $this->assertTrue($extension->activate_extension());
        $this->assertSame('update', $this->environment->db->calls[2][0]);
        $this->assertTrue($extension->update_extension('1.0'));
        $this->assertTrue($this->invoke($extension, 'unregister_extension', array('entry_saved')));
    }

    /**
     * Store the installed extension version when no hooks need registration.
     *
     * @return void
     */
    public function testExtensionWithNoHooksStillUpdatesItsVersion()
    {
        $extension = $this->subject('ExtensionSubject');
        $this->assertTrue($extension->activateExtension());
        $this->assertSame(array(array('update', 'extensions', array('version' => '1.2.3'), array('class' => ExtensionSubject::class))), $this->environment->db->calls);
    }

    /**
     * Require the legacy registration alias to preserve explicitly passed options.
     *
     * @return void
     */
    public function testLegacyRegistrationForwardsOptions()
    {
        $this->environment->db->results = array(array());
        $this->invoke($this->subject('ExtensionSubject'), 'register_extension', array('callback', 'custom_hook', 3, 'n'));
        $this->assertSame('custom_hook', $this->environment->db->calls[1][2]['hook']);
        $this->assertSame(3, $this->environment->db->calls[1][2]['priority']);
        $this->assertSame('n', $this->environment->db->calls[1][2]['enabled']);
    }

    /**
     * Return model, EE2 parameter, and new-entry metadata variants.
     *
     * @return array
     */
    public static function tabEntries()
    {
        return array(array((object) array('entry_id' => 12, 'channel_id' => 3, 'site_id' => 1), array(), array('entry_id' => 12, 'channel_id' => 3, 'site_id' => 1)), array(null, array('entry_id' => 13, 'meta' => array('channel_id' => 4, 'site_id' => 2)), array('entry_id' => 13, 'channel_id' => 4, 'site_id' => 2)), array(null, array(), array('entry_id' => 0, 'channel_id' => 0, 'site_id' => 0)));
    }

    /**
     * Map both generations of tab callbacks to the same entry variables.
     *
     * @dataProvider tabEntries
     * @param object|null $entry
     * @param array $params
     * @param array $expected
     * @return void
     */
    public function testTabEntryVariables($entry, $params, $expected)
    {
        $tab = $this->subject('TabSubject');
        $this->assertSame($expected, $tab->getEntryVariables($entry, $params));
        $this->assertSame(array(), $tab->display(3, 12));
        $this->assertTrue($tab->save($entry, $params));
    }

    /**
     * Preserve fieldtype accepted-content and settings display contracts.
     *
     * @dataProvider legacyContexts
     * @param bool $legacy
     * @return void
     */
    public function testFieldtypeSettings($legacy)
    {
        $field = $this->subject('FieldtypeSubject', $legacy);
        $field->flux->shortname = 'low_title';
        $this->assertTrue($field->update());
        $this->assertTrue($field->accepts_content_type('channel'));
        $this->assertFalse($field->accepts_content_type('file'));
        $field->displaySettingsRow('Title', '<input>', 'Description');
        $field->_display_settings_add_row('Wide', '<wide>', '', true);
        if ($legacy) {
            $this->assertSame(array(array('add_row', 'Title', '<input>'), array('add_row', array('colspan' => '2', 'data' => 'Wide<wide>'))), $this->environment->table->calls);
            $this->assertTrue($field->displaySettingsReturn());
            $this->assertTrue($field->_package_display_settings('custom', 'group', 'label'));
            return;
        }
        $this->assertCount(2, $field->rows);
        $this->assertSame(array('field_options_low_title' => array('label' => 'field_options', 'group' => 'low_title', 'settings' => $field->rows)), $field->displaySettingsReturn());
        $this->assertSame(array('custom' => array('label' => 'label', 'group' => 'group', 'settings' => $field->rows)), $field->_package_display_settings('custom', 'group', 'label'));
    }

    /**
     * Return channel ID resolution contexts for existing and new entries.
     *
     * @return array
     */
    public static function channelIds()
    {
        return array(array(true, array(), '8', null, '', '8'), array(false, array('field_channel_id' => 3), '8', null, '', 3), array(false, array(), '8', null, '', '8'), array(false, array(), false, null, 'cp/publish/create/9', 9), array(false, array(), false, null, 'cp/publish/edit', false), array(false, array(), false, 12, '', 4));
    }

    /**
     * Prefer configured channels and fall back to an entry's model only when needed.
     *
     * @dataProvider channelIds
     * @param bool $legacy
     * @param array $settings
     * @param mixed $input
     * @param int|null $entryId
     * @param string $uri
     * @param mixed $expected
     * @return void
     */
    public function testFieldtypeChannelId($legacy, $settings, $input, $entryId, $uri, $expected)
    {
        $field = $this->subject('FieldtypeSubject', $legacy);
        $field->settings = $settings;
        $field->setContentId($entryId);
        $this->environment->input->getValues = array('channel_id' => $input);
        $this->environment->uri->path = $uri;
        $model = new Recorder;
        $model->returns = array('get' => $model, 'filter' => $model, 'first' => (object) array('channel_id' => 4));
        $this->environment->services['Model'] = $model;
        $this->assertSame($expected, $field->getChannelId());
        $this->assertSame($entryId ? array(array('get', 'ChannelEntry'), array('filter', 'entry_id', $entryId), array('first')) : array(), $model->calls);
    }

    /**
     * Install module metadata only when the extension has no independent settings.
     *
     * @return void
     */
    public function testUpdaterInstallAndNoopUpdateGuards()
    {
        $updater = $this->subject('UpdaterSubject');
        $this->assertTrue($updater->install());
        $this->assertSame(array(array('insert', 'modules', array('module_name' => 'Low_title', 'module_version' => '1.2.3', 'has_cp_backend' => 'y', 'has_publish_fields' => 'n'))), $this->environment->db->calls);
        $updater->ext_settings = 'y';
        $this->assertTrue($updater->install());
        $this->assertCount(1, $this->environment->db->calls);
        $this->assertFalse($updater->update(''));
        $this->assertFalse($updater->update('1.2.3'));
    }

    /**
     * Delete all module references with site-role-era and EE2 menu semantics.
     *
     * @dataProvider legacyContexts
     * @param bool $legacy
     * @return void
     */
    public function testUpdaterUninstall($legacy)
    {
        $updater = $this->subject('UpdaterSubject', $legacy);
        $this->environment->db->results = array(array(array('module_id' => 7)));
        $this->assertTrue($updater->uninstall());
        $this->assertContains(array('where', 'module_id', 7), $this->environment->db->calls);
        $this->assertContains(array('delete', 'module_member_roles'), $this->environment->db->calls);
        $this->assertContains(array('delete', 'actions'), $this->environment->db->calls);
        $this->assertContains(array('delete', 'extensions'), $this->environment->db->calls);
        $this->assertSame(!$legacy, in_array(array('delete', 'menu_items'), $this->environment->db->calls, true));
    }
    /**
     * Construct plugin/module/extension/tab subjects through the real bridge settings.
     *
     * @return void
     */
    public function testConstructorsUseRealFluxConfiguration()
    {
        StaticCache::put('app_settings', array('version' => '1.2.3', 'name' => 'Low Title', 'description' => 'Title lookup', 'docs_url' => 'https://eeharbor.com'));
        foreach (array(Plugin::class, ModuleSubject::class, ExtensionSubject::class, TabSubject::class, McpSubject::class) as $class) {
            $subject = new $class;
            $this->assertInstanceOf(\Low\Title\FluxCapacitor\FluxCapacitor::class, $subject->flux);
            $this->assertSame('1.2.3', $subject->version);
        }
        $this->assertSame('Low Title', (new ExtensionSubject)->name);
    }

    /**
     * Construct fieldtype and updater subjects through their real archive collaborators.
     *
     * @return void
     */
    public function testFieldtypeAndUpdaterConstructors()
    {
        StaticCache::put('app_settings', array('version' => '1.2.3'));
        StaticCache::put('verifyLicenseTable', false);
        $fieldtype = new FieldtypeSubject;
        $this->assertSame('1.2.3', $fieldtype->version);
        $this->assertInstanceOf(\Low\Title\FluxCapacitor\Conduit\GlobalFieldSettings::class, $fieldtype->GlobalFieldSettings);
        $this->environment->db->results = array(array(), array());
        $this->environment->cp = new Recorder;
        $this->environment->cp->returns = array('get_foot' => array(), 'add_to_foot' => null);
        $updater = new UpdaterSubject;
        $this->assertSame('1.2.3', $updater->version);
    }

    /**
     * Require an update to tolerate this plugin's absent extension file under E_ALL.
     *
     * @return void
     */
    public function testUpdaterWithoutExtension()
    {
        $this->assertTrue($this->subject('UpdaterSubject')->update('1.0.0'));
    }

}
