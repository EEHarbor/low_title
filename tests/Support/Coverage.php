<?php

namespace LowTitleTests;

class Coverage
{
    /**
     * Start optional source-only branch instrumentation before native loading.
     *
     * @param string $root
     * @return void
     * @throws \RuntimeException
     */
    public static function start($root)
    {
        $report = getenv('LOW_TITLE_BRANCH_REPORT');
        if (!$report) {
            return;
        }
        if (!function_exists('xdebug_start_code_coverage')) {
            throw new \RuntimeException('Xdebug branch instrumentation is required');
        }
        xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE | XDEBUG_CC_BRANCH_CHECK);
        register_shutdown_function(static function () use ($report, $root) {
            self::save($report, $root . '/low_title/');
        });
    }

    /**
     * Persist source graphs without assigning counters to another declaration.
     *
     * @param string $report
     * @param string $sourceRoot
     * @return void
     * @throws \RuntimeException
     */
    private static function save($report, $sourceRoot)
    {
        $data = xdebug_get_code_coverage();
        xdebug_stop_code_coverage();
        $data = array_filter($data, static function ($file) use ($sourceRoot) {
            return strpos($file, $sourceRoot) === 0 || strpos($file, 'phar://' . $sourceRoot . 'low_title.phar/') === 0;
        }, ARRAY_FILTER_USE_KEY);
        $observed = false;
        foreach ($data as $file) {
            foreach (isset($file['functions']) ? $file['functions'] : array() as $function) {
                foreach ($function['branches'] as $branch) {
                    $observed = $observed || $branch['hit'] > 0;
                }
            }
        }
        if (!$observed) {
            throw new \RuntimeException('No shipped-source function graph was executed');
        }
        $context = array('ee_version' => defined('APP_VER') ? APP_VER : null, 'request' => defined('REQ') ? REQ : null);
        $bridgeClass = 'Low\\Title\\FluxCapacitor\\Bridge\\EE';
        if (class_exists($bridgeClass, false)) {
            $constructor = new \ReflectionMethod($bridgeClass, '__construct');
            $context['bridge_constructor'] = array('start_line' => $constructor->getStartLine(), 'end_line' => $constructor->getEndLine());
        }
        file_put_contents($report, json_encode(array('runtime' => PHP_VERSION, 'xdebug' => phpversion('xdebug'), 'context' => $context, 'files' => $data), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
