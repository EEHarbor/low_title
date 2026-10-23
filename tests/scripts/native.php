<?php

error_reporting(E_ALL);
ini_set('display_errors', 'stderr');
$case = $argv[1];
try {
    require dirname(__DIR__) . '/bootstrap.php';
    if ($case === 'constructor') {
        ee()->db->results = array(array());
        new Low_title;
    }
    if (strpos($case, 'construct:') === 0) {
        $class = 'Low\\Title\\FluxCapacitor\\' . str_replace('/', '\\', substr($case, 10));
        \Low\Title\FluxCapacitor\Conduit\StaticCache::put('app_settings', array('version' => '1.2.3', 'name' => 'Low Title', 'description' => 'Title lookup', 'docs_url' => 'https://eeharbor.com'));
        \Low\Title\FluxCapacitor\Conduit\StaticCache::put('verifyLicenseTable', false);
        ee()->db->results = array(array(), array());
        ee()->cp = new \LowTitleTests\Recorder;
        ee()->cp->returns = array('get_foot' => array(), 'add_to_foot' => null);
        if ($class === 'Low\\Title\\FluxCapacitor\\Base\\Ft') {
            class NativeFieldtype extends \Low\Title\FluxCapacitor\Base\Ft {}
            $class = NativeFieldtype::class;
        }
        new $class;
    }
    if ($case === 'entry-property') {
        ee()->db->results = array(array());
        (new ReflectionClass(Low_title::class))->newInstanceWithoutConstructor()->entry();
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
