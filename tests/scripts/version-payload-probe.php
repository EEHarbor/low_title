<?php

error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);
require dirname(__DIR__) . '/Support/Encryption.php';
require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/Support/BaseSubjects.php';

$case = $argv[1];
$GLOBALS['low_title_encryptions'] = array();
\Low\Title\FluxCapacitor\Conduit\StaticCache::clear();
\Low\Title\FluxCapacitor\Conduit\StaticCache::put('app_settings', array('version' => '1.2.3'));
ee()->config->config['site_id'] = 19;
ee()->config->config['base_url'] = $case === 'oversized' ? 'https://example.test/' . str_repeat('subdirectory/', 50) : 'https://example.test';
ee()->config->config['site_license_key'] = $case === 'pro-site' ? 'SITE-KEY' : ($case === 'site-zero' ? '0' : false);
ee()->db->tableExists = true;
ee()->db->fieldExists = true;
ee()->db->results = array(array(array('Column_name' => strpos($case, 'corrupt') !== false ? 'site_id' : 'license_key', 'Non_unique' => '0')));
ee()->cp = new \LowTitleTests\Recorder;
ee()->cp->returns = array('get_foot' => array(), 'add_to_foot' => null);
$result = array('case' => $case, 'ok' => false);
$status = 0;
try {
    if ($case === 'cached-valid' || $case === 'cached-corrupt') {
        $prime = new \LowTitleTests\VersionSubject;
        $result['cache_prime_verification'] = $prime->verifyLicenseTable();
        $result['cache_prime_calls'] = ee()->db->calls;
        ee()->db->calls = array();
    }
    $version = new \LowTitleTests\VersionSubject;
    $result['constructor_calls'] = ee()->db->calls;
    $result['verification'] = $version->verifyLicenseTable();
    $pro = array();
    if ($case === 'pro-site' || $case === 'pro-null') {
        $pro = array(array('module_version' => $case === 'pro-site' ? '3.0.0' : null));
    }
    ee()->db->results = array($pro, array(array('license_key' => 'ABC-123 !')));
    $version->check();
    $result['ok'] = true;
} catch (Throwable $exception) {
    $result += array('class' => get_class($exception), 'message' => $exception->getMessage(), 'file' => $exception->getFile(), 'line' => $exception->getLine());
    $status = 1;
}
$result['encryptions'] = $GLOBALS['low_title_encryptions'];
$result['cp_calls'] = ee()->cp->calls;
$result['database_calls'] = ee()->db->calls;
$result['archive_sha256'] = hash_file('sha256', dirname(__DIR__, 2) . '/low_title/low_title.phar');
$result['source_file'] = (new ReflectionClass(\Low\Title\FluxCapacitor\Conduit\Version::class))->getFileName();
$result['spy_file'] = (new ReflectionFunction('Low\\Title\\FluxCapacitor\\Conduit\\openssl_public_encrypt'))->getFileName();
echo json_encode($result);
exit($status);
