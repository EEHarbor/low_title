<?php

namespace LowTitleTests;

error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);

class NavigationProbe
{
    /**
     * Install independent request state before each ordinary provider flow.
     *
     * @param string $segment
     * @return void
     */
    private static function environment($segment)
    {
        $GLOBALS['low_title_ee'] = new Environment;
        ee()->db->results = array(array());
        ee()->uri = new Uri;
        ee()->uri->path = 'cp/addons/settings/low_title' . ($segment === 'low_title' ? '' : '/' . $segment);
        ee()->view = new \stdClass;
        ee()->view->cp_breadcrumbs = array();
        ee()->cp = new NavigationControlPanel;
        ee()->services['CP/Sidebar'] = new NavigationSidebar;
        $urls = new NavigationUrlFactory(ee()->uri->uri_string());
        ee()->services['CP/URL'] = static function ($path = null, $variables = array()) use ($urls) {
            return $path === null ? $urls : $urls->make($path, $variables);
        };
        \Low\Title\FluxCapacitor\Conduit\StaticCache::clear();
    }

    /**
     * Configure the provider using its public extension hooks.
     *
     * @param string $segment
     * @param array|false|null $items
     * @param array|false|null $map
     * @param array|false|null $buttons
     * @param bool $defer
     * @return \Low\Title\Conduit\ConfiguredNav
     */
    private static function configured($segment, $items, $map = array(), $buttons = array(), $defer = false)
    {
        self::environment($segment);
        $GLOBALS['low_title_nav_options'] = array('items' => $items, 'map' => $map, 'buttons' => $buttons, 'defer' => $defer);

        return new \Low\Title\Conduit\ConfiguredNav;
    }

    /**
     * Export a bounded CP route without claiming full URL-rendering integration.
     *
     * @param mixed $url
     * @return mixed
     */
    private static function url($url)
    {
        return $url instanceof Url ? array('path' => $url->path, 'variables' => $url->variables) : $url;
    }

    /**
     * Preserve header state, active identity and page metadata in the result.
     *
     * @param \Low\Title\FluxCapacitor\Conduit\McpNav $nav
     * @return array
     */
    private static function describe($nav)
    {
        $items = array();
        foreach ((array) $nav->nav_items as $method => $header) {
            $button = $header->button;
            if ($button !== null) {
                $button['url'] = self::url($button['url']);
            }
            $items[$method] = array('text' => $header->text, 'url' => self::url($header->url), 'button' => $button, 'active' => $header->class);
        }
        $breadcrumbs = array();
        foreach (ee()->cp->breadcrumbs as $breadcrumb) {
            $breadcrumbs[] = array(self::url($breadcrumb[0]), $breadcrumb[1]);
        }
        $active = array_search($nav->getActiveItem(), (array) $nav->nav_items, true);

        return array('items' => $items, 'active' => $active === false ? null : $active, 'title' => isset(ee()->view->cp_page_title) ? ee()->view->cp_page_title : null, 'breadcrumbs' => $breadcrumbs, 'breadcrumb_map' => ee()->view->cp_breadcrumbs, 'events' => $nav instanceof \Low\Title\Conduit\ConfiguredNav ? $nav->events : array());
    }

    /**
     * Exercise supported navigation defaults, aliases, actions and lifecycle hooks.
     *
     * @param string $case
     * @return array
     * @throws \RuntimeException
     */
    public static function run($case)
    {
        self::environment('low_title');
        if ($case === 'cold-provider') {
            $controller = new McpSubject;

            return array('version' => $controller->version, 'nav_class' => get_class($controller->nav), 'nav' => self::describe($controller->nav));
        }
        if ($case === 'native-properties') {
            $nav = new \Low\Title\Conduit\NativeNavWithoutProperties;

            return self::describe($nav);
        }
        if ($case === 'http-route') {
            $nav = new \Low\Title\Conduit\McpNav;

            return array('url' => self::url($nav->getUrl('http_logs')));
        }
        $items = array('/' => 'Home', 'history' => 'History', 'HEADING-tools' => 'Tools', 'https://docs.example.test' => 'Docs', 'license' => 'My license');
        if ($case === 'legacy') {
            $nav = self::configured('history', $items);

            return array('right_nav' => ee()->cp->rightNav, 'events' => $nav->events, 'sidebar' => isset($nav->sidebar), 'active' => $nav->getActiveItem());
        }
        if ($case !== 'modern') {
            throw new \RuntimeException('Unknown navigation context');
        }
        $controller = new McpSubject;
        $result = array('controller' => array('version' => $controller->version, 'nav_class' => get_class($controller->nav), 'nav' => self::describe($controller->nav)));
        $buttons = array('history' => array('create' => 'Create', 'import' => 'Import'), 'index' => array());
        $nav = self::configured('history', $items, array('edit' => 'history'), $buttons);
        $result['direct'] = self::describe($nav);
        $active = $nav->getActiveItem();
        $active->withButton('Changed', '/changed');
        $result['active_identity'] = $active === $nav->nav_items['history'] && $nav->nav_items['history']->button === array('text' => 'Changed', 'url' => '/changed');
        $result['alias'] = self::describe(self::configured('edit', $items, array('edit' => 'history'), $buttons));
        $nav = self::configured('low_title', array('' => 'Empty-key home'));
        $nav->formatItems();
        $result['automatic_index'] = self::describe($nav);
        $result['slash_alias'] = self::describe(self::configured('overview', array('/' => 'Slash home'), array('overview' => '/')));
        $result['empty_alias'] = self::describe(self::configured('overview', array('index' => 'Index'), array('overview' => '')));
        $result['explicit_shortname'] = self::describe(self::configured('low_title', $items, array('low_title' => 'history')));
        $result['unmatched'] = self::describe(self::configured('missing', $items));
        $result['false_defaults'] = self::describe(self::configured('missing', false, false, false));
        $result['null_defaults'] = self::describe(self::configured('missing', null, null, null));
        $result['empty_title'] = self::describe(self::configured('history', array('history' => '')));
        $result['null_title'] = self::describe(self::configured('history', array('history' => null)));
        $result['alias_overrides_direct'] = self::describe(self::configured('history', array('index' => 'Home', 'history' => 'History'), array('history' => 'index')));
        $nav = self::configured('history', $items, array(), array(), true);
        $result['deferred_before'] = array('sidebar' => isset($nav->sidebar), 'events' => $nav->events, 'active' => $nav->getActiveItem());
        $nav->generateNav();
        $result['deferred_after'] = self::describe($nav);
        $nav->postGenerateNav();
        $result['manual_post_events'] = $nav->events;
        $nav->setToolbarIcon();
        $result['toolbar_default'] = ee()->view->header;
        $result['toolbar_default']['toolbar_items']['settings']['href'] = self::url($result['toolbar_default']['toolbar_items']['settings']['href']);
        $nav->setToolbarIcon('Documentation', 'https://docs.example.test', 'help');
        $result['toolbar_custom'] = ee()->view->header;
        $nav->setToolbarIcon('', '/', 'home');
        $result['toolbar_empty_title'] = ee()->view->header;
        $result['toolbar_empty_title']['toolbar_items']['home']['href'] = self::url($result['toolbar_empty_title']['toolbar_items']['home']['href']);
        $result['urls'] = array(self::url($nav->getUrl()), self::url($nav->getUrl('/')), self::url($nav->getUrl('')), self::url($nav->getUrl('https://docs.example.test')));

        return $result;
    }
}

$root = dirname(__DIR__, 2);
$case = $argv[1];
$directory = $argv[2];
$result = array('ok' => false, 'case' => $case, 'fixture_root' => $directory);
$exit = 0;
try {
    mkdir($directory . '/low_title/Conduit', 0700, true);
    foreach (array('low_title.phar', 'addon.setup.php', 'addon.json', 'autoload.php') as $file) {
        symlink($root . '/low_title/' . $file, $directory . '/low_title/' . $file);
    }
    symlink($root . '/tests/fixtures/navigation/McpNav.php', $directory . '/low_title/Conduit/McpNav.php');
    define('BASEPATH', $directory . '/');
    define('PATH_THIRD', $directory . '/');
    define('PATH_CACHE', $directory . '/cache/');
    define('FILE_WRITE_MODE', 0600);
    define('DIR_WRITE_MODE', 0700);
    define('APP_VER', $case === 'legacy' ? '2.5.0' : '7.5.0');
    define('REQ', 'CP');
    define('BASE', '/admin.php?S=0');
    define('AMP', '&amp;');
    define('SYSPATH', $directory . '/system/');
    define('APPPATH', $directory . '/app/');
    require $root . '/tests/bootstrap.php';
    require $root . '/tests/Support/Navigation.php';
    if ($case !== 'cold-provider') {
        require $directory . '/low_title/Conduit/McpNav.php';
    }
    require $root . '/tests/Support/BaseSubjects.php';
    $result['data'] = NavigationProbe::run($case);
    $result['ok'] = true;
} catch (\Throwable $exception) {
    $result += array('class' => get_class($exception), 'message' => $exception->getMessage(), 'file' => $exception->getFile(), 'line' => $exception->getLine());
    $exit = 1;
} finally {
    $result['archive_sha256'] = hash_file('sha256', $root . '/low_title/low_title.phar');
    $result['source_file'] = class_exists('Low\\Title\\FluxCapacitor\\Conduit\\McpNav', false) ? (new \ReflectionClass('Low\\Title\\FluxCapacitor\\Conduit\\McpNav'))->getFileName() : null;
    $result['provider_file'] = class_exists('Low\\Title\\Conduit\\McpNav', false) ? (new \ReflectionClass('Low\\Title\\Conduit\\McpNav'))->getFileName() : null;
    if (is_dir($directory . '/low_title/Conduit')) {
        if (is_link($directory . '/low_title/Conduit/McpNav.php')) {
            unlink($directory . '/low_title/Conduit/McpNav.php');
        }
        rmdir($directory . '/low_title/Conduit');
    }
    foreach (array('low_title.phar', 'addon.setup.php', 'addon.json', 'autoload.php') as $file) {
        if (is_link($directory . '/low_title/' . $file)) {
            unlink($directory . '/low_title/' . $file);
        }
    }
    if (is_dir($directory . '/low_title')) {
        rmdir($directory . '/low_title');
    }
    $result['fixtures_remaining'] = iterator_count(new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS));
}
echo json_encode($result);
exit($exit);
