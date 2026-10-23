<?php

namespace LowTitleTests;

require_once dirname(__DIR__, 2) . '/Support/BaseSubjects.php';

class FieldSettingsTest extends TestCase
{
    /**
     * Return persisted settings including missing, false, and nested values.
     *
     * @return array
     */
    public static function settings()
    {
        return array(array(array(), null), array(array(array('settings' => base64_encode(serialize(false)))), array()), array(array(array('settings' => base64_encode(serialize(array('enabled' => false, 'nested' => array('value' => 3)))))), array('enabled' => false, 'nested' => array('value' => 3))));
    }

    /**
     * Decode only persisted fieldtype settings and preserve their value types.
     *
     * @dataProvider settings
     * @param array $rows
     * @param array|null $expected
     * @return void
     */
    public function testSettingsRead($rows, $expected)
    {
        $settings = new FieldSettingsSubject('low_title');
        $this->environment->db->results = array($rows, $rows);
        $this->assertSame($expected, $settings->getSettings());
        $this->assertSame($expected ? false : null, $settings->getSetting('enabled'));
        $this->assertSame(array('where', 'name', 'low_title'), $this->environment->db->calls[1]);
    }

    /**
     * Preserve existing settings when changing one named value.
     *
     * @return void
     */
    public function testSettingUpdate()
    {
        $settings = new FieldSettingsSubject;
        $this->environment->db->results = array(array(array('settings' => base64_encode(serialize(array('other' => 'kept'))))));
        $this->assertTrue($settings->updateSetting('enabled', false));
        $this->assertSame(array('update', 'fieldtypes', array('settings' => base64_encode(serialize(array('other' => 'kept', 'enabled' => false))))), $this->environment->db->calls[4]);
        $this->environment->db->results = array(array());
        $this->assertFalse($settings->updateSetting('missing', true));
    }

    /**
     * Require unknown settings to yield null without a diagnostic hidden by @.
     *
     * @return void
     */
    public function testUnknownSettingReturnsNull()
    {
        $this->environment->db->results = array(array(array('settings' => base64_encode(serialize(array('other' => 'value'))))));
        $this->assertNull((new FieldSettingsSubject)->getSetting('missing'));
    }

    /**
     * Require malformed persisted serialization to be handled without warnings.
     *
     * @return void
     */
    public function testMalformedSettingsAreHandled()
    {
        $this->environment->db->results = array(array(array('settings' => base64_encode('malformed serialized settings'))));
        $this->assertSame(array(), (new FieldSettingsSubject)->getSettings());
    }
}
