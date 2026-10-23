<?php

namespace LowTitleTests;

class NavigationTest extends TestCase
{
    /**
     * Preserve each URL service's captured request and independent route objects.
     *
     * @return void
     */
    public function testUrlFactoryRetainsItsInitialRequest()
    {
        require_once dirname(__DIR__, 2) . '/Support/Navigation.php';
        $this->environment->uri = new Uri;
        $this->environment->uri->path = 'cp/first';
        $factory = new NavigationUrlFactory($this->environment->uri->uri_string());
        $this->environment->uri->path = 'cp/second';
        $first = $factory->make('first');
        $second = $factory->make('second');
        $again = $factory->make('first');

        $this->assertNotSame($first, $second);
        $this->assertNotSame($first, $again);
        $this->assertSame('first', $first->path);
        $this->assertSame('second', $second->path);
        $this->assertTrue($first->isTheRequestedURI());
        $this->assertFalse($second->isTheRequestedURI());
        $this->assertTrue($again->isTheRequestedURI());
        $header = (new NavigationSidebar)->addHeader('First');
        $this->assertSame($header, $header->withUrl($first));
        $this->assertSame('active ', $header->class);
        $this->assertSame($header, $header->withUrl($second));
        $this->assertSame('active ', $header->class);
        $replacement = new NavigationUrlFactory($this->environment->uri->uri_string());
        $this->assertTrue($replacement->make('second')->isTheRequestedURI());
        $this->assertFalse($replacement->make('first')->isTheRequestedURI());
    }

    /**
     * Discover the optional provider through the controller's cold include path.
     *
     * @return void
     */
    public function testColdProviderDiscovery()
    {
        $data = $this->probe('cold-provider');
        $this->assertSame('4.0.2', $data['version']);
        $this->assertSame('Low\\Title\\Conduit\\McpNav', $data['nav_class']);
        $this->assertSame(array(), $data['nav']['items']);
        $this->assertNull($data['nav']['active']);
    }

    /**
     * Load the optional provider and preserve supported modern navigation flows.
     *
     * @return void
     */
    public function testModernNavigation()
    {
        $data = $this->probe('modern');
        $this->assertSame('4.0.2', $data['controller']['version']);
        $this->assertSame('Low\\Title\\Conduit\\McpNav', $data['controller']['nav_class']);
        $this->assertSame(array(), $data['controller']['nav']['items']);
        $direct = $data['direct'];
        $this->assertSame(array('index', 'history', 'HEADING-tools', 'https://docs.example.test', 'license'), array_keys($direct['items']));
        $this->assertSame($this->route('index'), $direct['items']['index']['url']);
        $this->assertSame($this->route('history'), $direct['items']['history']['url']);
        $this->assertSame(array('text' => 'Import', 'url' => $this->route('import')), $direct['items']['history']['button']);
        $this->assertSame('active active ', $direct['items']['history']['active']);
        $this->assertSame('History', $direct['title']);
        $this->assertSame('history', $direct['active']);
        $this->assertTrue($data['active_identity']);
        $this->assertSame('Tools', $direct['items']['HEADING-tools']['text']);
        $this->assertNull($direct['items']['HEADING-tools']['url']);
        $this->assertSame('https://docs.example.test', $direct['items']['https://docs.example.test']['url']);
        $this->assertSame(array(array($this->route('index'), 'Low Title')), $direct['breadcrumbs']);
        $this->assertSame(array('/cp/addons/settings/low_title/index' => 'Low Title'), $direct['breadcrumb_map']);
        $this->assertSame(array(array('pre', false), array('post', true)), $direct['events']);
        $this->assertSame('history', $data['alias']['active']);
        $this->assertSame('active ', $data['alias']['items']['history']['active']);
        foreach (array('automatic_index' => 'Empty-key home', 'slash_alias' => 'Slash home', 'empty_alias' => 'Index') as $context => $title) {
            $this->assertSame(array('index'), array_keys($data[$context]['items']));
            $this->assertSame('index', $data[$context]['active']);
            $this->assertSame($title, $data[$context]['title']);
        }
        $this->assertSame('history', $data['explicit_shortname']['active']);
        $this->assertSame('History', $data['explicit_shortname']['title']);
        foreach (array('unmatched', 'false_defaults', 'null_defaults') as $context) {
            $this->assertNull($data[$context]['active']);
            $this->assertNull($data[$context]['title']);
            $this->assertSame(array(), $data[$context]['breadcrumbs']);
        }
        $this->assertSame(array(), $data['false_defaults']['items']);
        $this->assertSame(array(), $data['null_defaults']['items']);
        $this->assertSame('', $data['empty_title']['title']);
        $this->assertSame(array(array($this->route('index'), 'Low Title')), $data['empty_title']['breadcrumbs']);
        $this->assertNull($data['null_title']['title']);
        $this->assertSame(array(), $data['null_title']['breadcrumbs']);
        $this->assertSame('index', $data['alias_overrides_direct']['active']);
        $this->assertSame('Home', $data['alias_overrides_direct']['title']);
        $this->assertSame(array('sidebar' => false, 'events' => array(array('pre', false)), 'active' => null), $data['deferred_before']);
        $this->assertSame('history', $data['deferred_after']['active']);
        $this->assertSame(array(array('pre', false)), $data['deferred_after']['events']);
        $this->assertSame(array(array('pre', false), array('post', true)), $data['manual_post_events']);
        $this->assertSame(array('title' => 'Low Title', 'toolbar_items' => array('settings' => array('href' => $this->route('settings'), 'title' => 'Low Title'))), $data['toolbar_default']);
        $this->assertSame(array('title' => 'Documentation', 'toolbar_items' => array('help' => array('href' => 'https://docs.example.test', 'title' => 'Documentation'))), $data['toolbar_custom']);
        $this->assertSame(array('title' => 'Low Title', 'toolbar_items' => array('home' => array('href' => $this->route('index'), 'title' => 'Low Title'))), $data['toolbar_empty_title']);
        $this->assertSame(array($this->route('index'), $this->route('index'), $this->route(''), 'https://docs.example.test'), $data['urls']);
    }

    /**
     * Delegate formatted items to the legacy right-hand navigation boundary.
     *
     * @return void
     */
    public function testLegacyNavigation()
    {
        $data = $this->probe('legacy');
        $base = '/admin.php?S=0&amp;C=addons_modules&amp;M=show_module_cp&amp;module=low_title&amp;method=';
        $this->assertSame(array('Home' => $base . 'index&amp;D=cp', 'History' => $base . 'history&amp;D=cp', 'Tools' => $base . 'HEADING-tools&amp;D=cp', 'Docs' => 'https://docs.example.test', 'My license' => $base . 'license&amp;D=cp'), $data['right_nav']);
        $this->assertSame(array(array('pre', false), array('post', false)), $data['events']);
        $this->assertFalse($data['sidebar']);
        $this->assertNull($data['active']);
    }

    /**
     * Keep a valid controller method containing http within the add-on route.
     *
     * @return void
     */
    public function testHttpNamedControllerRoute()
    {
        $data = $this->probe('http-route');
        $this->assertSame($this->route('http_logs'), $data['url']);
    }

    /**
     * Retain native undeclared-property diagnostics as an ordinary regression.
     *
     * @return void
     */
    public function testNativePropertyCompatibility()
    {
        $data = $this->probe('native-properties');
        $this->assertSame(array(), $data['items']);
        $this->assertNull($data['active']);
    }

    /**
     * Describe the expected local route independently of source routing helpers.
     *
     * @param string $method
     * @return array
     */
    private function route($method)
    {
        return array('path' => 'addons/settings/low_title' . ($method === '' ? '' : '/' . $method), 'variables' => array());
    }

    /**
     * Run an owned native context with bounded output, cleanup and strict diagnostics.
     *
     * @param string $case
     * @return array
     * @throws \RuntimeException
     */
    private function probe($case)
    {
        $directory = sys_get_temp_dir() . '/low-title-navigation-' . getmypid() . '-' . bin2hex(random_bytes(6));
        mkdir($directory, 0700);
        $root = dirname(__DIR__, 3);
        $command = array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'xdebug.mode=off', '-d', 'sys_temp_dir=' . $directory, $root . '/tests/scripts/navigation-probe.php', $case, $directory);
        try {
            $process = Process::run($command, $root, array_merge($_ENV, array('LOW_TITLE_COVERAGE_SOURCE' => '', 'LOW_TITLE_BRANCH_REPORT' => '', 'TMPDIR' => $directory)));
            $result = json_decode($process['stdout'], true);
            $this->assertIsArray($result, $process['stdout'] . $process['stderr']);
            $this->assertSame($case, $result['case']);
            $this->assertSame(realpath($directory), realpath($result['fixture_root']));
            $this->assertSame(hash_file('sha256', $root . '/low_title/low_title.phar'), $result['archive_sha256']);
            $this->assertSame('phar://' . $root . '/low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php', $result['source_file']);
            $this->assertSame(realpath($root . '/tests/fixtures/navigation/McpNav.php'), $result['provider_file']);
            $this->assertSame(0, $result['fixtures_remaining']);
            $this->assertSame(array(), iterator_to_array(new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS)));
            $this->assertFalse($process['timed_out']);
            $this->assertSame('', $process['stderr']);
            $this->assertSame(0, $process['exit'], $process['stdout']);
            $this->assertTrue($result['ok'], $process['stdout']);

            return $result['data'];
        } finally {
            $this->removeFixture($directory);
            $this->assertDirectoryDoesNotExist($directory);
        }
    }

    /**
     * Remove only this test's owned tree without following fixture symlinks.
     *
     * @param string $directory
     * @return void
     */
    private function removeFixture($directory)
    {
        foreach (new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS) as $entry) {
            if ($entry->isDir() && !$entry->isLink()) {
                $this->removeFixture($entry->getPathname());
                continue;
            }
            unlink($entry->getPathname());
        }
        rmdir($directory);
    }
}
