<?php

error_reporting(E_ALL);
ini_set('display_errors', 'stderr');
$case = $argv[1];
if ($case === 'bridge-version') {
    define('URL_THIRD_THEMES', 'https://cdn.example.test/third-party/');
}
try {
    require dirname(__DIR__) . '/bootstrap.php';
    if (strpos($case, 'extract:') === 0) {
        $data = 'payload';
        $stored = $case === 'extract:truncated' ? substr($data, 0, -1) : $data;
        $size = strlen($data) + ($case === 'extract:size' ? 1 : 0);
        $checksum = crc32($data) + ($case === 'extract:checksum' ? 1 : 0);
        $stream = fopen('php://memory', 'r+');
        try {
            fwrite($stream, $stored);
            rewind($stream);
            $entry = array($size, 0, strlen($data), sprintf('%u', $checksum & 0xffffffff), 0, 0, 0, 0);
            \Extract_low_title::extractFile('payload.bin', $entry, $stream);
        } finally {
            fclose($stream);
        }
    }
    if ($case === 'constructor') {
        ee()->db->results = array(array());
        new Low_title;
    }
    if (strpos($case, 'construct:') === 0) {
        $class = 'Low\\Title\\FluxCapacitor\\' . str_replace('/', '\\', substr($case, 10));
        $target = $class;
        \Low\Title\FluxCapacitor\Conduit\StaticCache::put('app_settings', array('version' => '1.2.3', 'name' => 'Low Title', 'description' => 'Title lookup', 'docs_url' => 'https://eeharbor.com'));
        \Low\Title\FluxCapacitor\Conduit\StaticCache::put('verifyLicenseTable', false);
        ee()->db->results = array(array(), array());
        ee()->cp = new \LowTitleTests\Recorder;
        ee()->cp->returns = array('get_foot' => array(), 'add_to_foot' => null);
        if ($class === 'Low\\Title\\FluxCapacitor\\Base\\Ft') {
            class NativeFieldtype extends \Low\Title\FluxCapacitor\Base\Ft {}
            $class = NativeFieldtype::class;
        }
        $instance = new $class;
        if (strpos($target, 'Low\\Title\\FluxCapacitor\\Base\\') === 0 && $instance->version !== '1.2.3') {
            throw new RuntimeException('The native constructor did not load its configured version');
        }
        if (REQ === 'CP' && in_array($target, array('Low\\Title\\FluxCapacitor\\Base\\Ft', 'Low\\Title\\FluxCapacitor\\Base\\Tab'), true)) {
            if (count(ee()->cp->calls) !== 2 || ee()->cp->calls[0][0] !== 'get_foot' || ee()->cp->calls[1][0] !== 'add_to_foot') {
                throw new RuntimeException('The CP constructor did not run its version pixel check');
            }
        }
    }
    if ($case === 'entry-property') {
        ee()->db->results = array(array());
        (new ReflectionClass(Low_title::class))->newInstanceWithoutConstructor()->entry();
    }
    if ($case === 'extension-menu') {
        $extension = (new ReflectionClass(\Low\Title\FluxCapacitor\Base\Ext::class))->newInstanceWithoutConstructor();
        $extension->flux = new \LowTitleTests\Recorder;
        $extension->flux->returns = array('getConfig' => 'Low Title', 'moduleURL' => '/low-title');
        $menu = new \LowTitleTests\Recorder;
        $menu->returns['addItem'] = null;
        $extension->cpCustomMenu($menu);
        if ($menu->calls !== array(array('addItem', 'Low Title', '/low-title'))) {
            throw new RuntimeException('The CP extension menu did not receive its label and module URL');
        }
    }
    if ($case === 'bridge-version') {
        $bridge = new \Low\Title\FluxCapacitor\Bridge\EE(array('module' => 'low_title', 'module_name' => 'Low Title'));
        $major = substr(APP_VER, 0, 1);
        $parent = new ReflectionClass($bridge);
        if ($parent->getParentClass()->getShortName() !== 'EE' . ($major === '7' ? '6' : $major) || $bridge->getEEVersion(false) !== APP_VER) {
            throw new RuntimeException('The shipped selector did not choose the matching EE bridge');
        }
        if ($bridge->getAddonThemesDir() !== URL_THIRD_THEMES . 'low_title/') {
            throw new RuntimeException('The bridge did not honor the configured third-party theme URL');
        }
    }
    if ($case === 'archive-index') {
        $callback = null;
        foreach (spl_autoload_functions() as $loader) {
            if ($loader instanceof Closure && strpos((new ReflectionFunction($loader))->getFileName(), '.phar/index.php') !== false) {
                $callback = $loader;
            }
        }
        if ($callback === null) {
            throw new RuntimeException('The archive did not register its own fallback loader');
        }
        if ($callback('OtherVendor\\Example') !== null || $callback('Low\\Title\\NotShipped') !== null) {
            throw new RuntimeException('The archive fallback loader did not ignore absent classes');
        }
        $callback('Low\\Title\\FluxCapacitor\\Base\\Tab');
        if (!class_exists('Low\\Title\\FluxCapacitor\\Base\\Tab', false)) {
            throw new RuntimeException('The archive fallback loader did not load its shipped member');
        }
    }
    if ($case === 'updater-uninstall') {
        require dirname(__DIR__) . '/Support/BaseSubjects.php';
        $updater = (new ReflectionClass(\LowTitleTests\UpdaterSubject::class))->newInstanceWithoutConstructor();
        $updater->flux = new \Low\Title\FluxCapacitor\FluxCapacitor;
        ee()->db->results = array(array(array('module_id' => 7)));
        if ($updater->uninstall() !== true) {
            throw new RuntimeException('The updater did not report a successful uninstall');
        }
        $membership = version_compare(APP_VER, '6.0', '>=') ? 'module_member_roles' : 'module_member_groups';
        $calls = ee()->db->calls;
        $module = \LowTitleTests\UpdaterSubject::class;
        $expected = array(array('select', 'module_id'), array('from', 'modules'), array('where', 'module_name', $module), array('get'), array('where', 'module_id', 7), array('delete', $membership), array('where', 'module_name', $module), array('delete', 'modules'), array('where', 'class', $module), array('delete', 'actions'), array('where', 'class', $module . '_ext'), array('delete', 'extensions'));
        if (!$updater->flux->is_ee2()) {
            $expected[] = array('where', 'data', $module . '_ext');
            $expected[] = array('delete', 'menu_items');
        }
        if ($calls !== $expected) {
            throw new RuntimeException('Uninstall did not bind every mutation to its owning module and EE-era table');
        }
    }
    if ($case === 'static-info') {
        Low_title::info();
    }
    if ($case === 'load-all') {
        $phar = new Phar(dirname(__DIR__, 2) . '/low_title/low_title.phar');
        foreach (new RecursiveIteratorIterator($phar) as $file) {
            $relative = substr($file->getPathname(), strpos($file->getPathname(), '.phar/') + 6);
            if ($file->getExtension() !== 'php' || strpos($relative, 'FluxCapacitor/') !== 0) {
                continue;
            }
            $class = 'Low\\Title\\' . str_replace('/', '\\', substr($relative, 0, -4));
            if (!class_exists($class) && !interface_exists($class)) {
                throw new RuntimeException('Unable to load ' . $class);
            }
        }
    }
    if (strpos($case, 'load:') === 0) {
        $class = 'Low\\Title\\' . str_replace('/', '\\', substr($case, 5));
        if (!class_exists($class)) {
            throw new RuntimeException('Unable to load ' . $class);
        }
    }
    if ($case === 'suppressed-diagnostic') {
        @trigger_error('strict harness diagnostic probe', E_USER_DEPRECATED);
    }
    if ($case === 'null-title') {
        ee()->db->results = array(array(array('title' => null)));
        $result = (new ReflectionClass(\LowTitleTests\Plugin::class))->newInstanceWithoutConstructor()->entry();
        if ($result !== '') {
            throw new RuntimeException('Null title was not normalized to an empty tag result');
        }
    }
    if ($case === 'missing-pages-uris') {
        ee()->TMPL->tagparams = array('pages_uri' => '/about/');
        ee()->config->config['site_pages'] = array(1 => array());
        ee()->db->results = array(array());
        (new ReflectionClass(\LowTitleTests\Plugin::class))->newInstanceWithoutConstructor()->entry();
    }
    echo json_encode(array('ok' => true, 'case' => $case));
} catch (Throwable $exception) {
    echo json_encode(array('ok' => false, 'case' => $case, 'class' => get_class($exception), 'message' => $exception->getMessage(), 'file' => $exception->getFile(), 'line' => $exception->getLine(), 'severity' => $exception instanceof ErrorException ? $exception->getSeverity() : null));
    exit(1);
}
