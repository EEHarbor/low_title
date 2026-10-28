<?php

namespace LowTitleTests;

class LicenseViewTest extends TestCase
{
    /**
     * Return the add-on license template across supported EE markup versions.
     *
     * @return array
     */
    public static function views()
    {
        $cases = array();
        foreach (array('low_title/views/license.php') as $path) {
            foreach (array(2, 3, 7) as $version) {
                foreach (array('KEY-123', 'low-title-ignore-site-1') as $key) {
                    $cases[] = array($path, $version, $key);
                }
            }
        }

        return $cases;
    }

    /**
     * Render license form data and hide the internal ignored-site key.
     *
     * @dataProvider views
     * @param string $path
     * @param int $ee_ver
     * @param string $license_key
     * @return void
     */
    public function testLicenseView($path, $ee_ver, $license_key)
    {
        $action_url = '/save-license';
        $ignore_site = strpos($license_key, 'ignore-site-') !== false;
        $alerts = $this->getMockBuilder(\stdClass::class)->addMethods(array('getAllInlines'))->getMock();
        $alerts->expects($ee_ver > 2 ? $this->once() : $this->never())->method('getAllInlines')->willReturn('<alert>Saved</alert>');
        $this->environment->services['CP/Alert'] = $alerts;
        $root = dirname(__DIR__, 3) . '/';
        $file = $root . $path;
        $hadFormCalls = array_key_exists('low_title_form_calls', $GLOBALS);
        $previousFormCalls = $hadFormCalls ? $GLOBALS['low_title_form_calls'] : null;
        $GLOBALS['low_title_form_calls'] = array();
        ob_start();
        try {
            include $file;
            $html = ob_get_contents();
            $formCalls = $GLOBALS['low_title_form_calls'];
        } finally {
            ob_end_clean();
            if ($hadFormCalls) {
                $GLOBALS['low_title_form_calls'] = $previousFormCalls;
            } else {
                unset($GLOBALS['low_title_form_calls']);
            }
        }
        $this->assertStringContainsString('box ee' . $ee_ver . ' addon-license', $html);
        $this->assertStringContainsString('Save License Key', $html);
        $this->assertSame(array(array('open', $action_url, array('class' => 'settings panel-body')), array('input', 'license_key', $ignore_site ? '' : $license_key), array('checkbox', 'ignore_site', '1', $ignore_site), array('close')), $formCalls);
    }
}
