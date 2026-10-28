<?php

namespace LowTitleTests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected $environment;
    private $callerGlobals;
    private $callerIncludePath;
    private $callerAutoloaders;
    private $callerReporting;

    /**
     * Reset request collaborators and enforce strict diagnostics for each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->callerReporting = error_reporting();
        error_reporting(E_ALL);
        $this->callerGlobals = array();
        foreach (array('_GET', '_POST', '_REQUEST', '_SERVER', '_COOKIE', '_FILES', '_ENV', '_SESSION', 'low_title_ee', 'low_title_form_calls') as $name) {
            $this->callerGlobals[$name] = array(array_key_exists($name, $GLOBALS), isset($GLOBALS[$name]) ? $GLOBALS[$name] : null);
        }
        $this->callerIncludePath = get_include_path();
        $this->callerAutoloaders = spl_autoload_functions() ?: array();
        $this->environment = new Environment;
        $GLOBALS['low_title_ee'] = $this->environment;
        set_error_handler(array(Environment::class, 'diagnostic'), E_ALL);
    }

    /**
     * Restore caller globals, request data, include path, loaders, and diagnostic handler.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        foreach ($this->callerGlobals as $name => $state) {
            if ($state[0]) {
                $GLOBALS[$name] = $state[1];
                continue;
            }
            unset($GLOBALS[$name]);
        }
        set_include_path($this->callerIncludePath);
        foreach (spl_autoload_functions() ?: array() as $loader) {
            spl_autoload_unregister($loader);
        }
        foreach ($this->callerAutoloaders as $loader) {
            spl_autoload_register($loader);
        }
        restore_error_handler();
        error_reporting($this->callerReporting);
    }

    /**
     * Isolate query behavior from the independently tested native constructor.
     *
     * @return Plugin
     */
    protected function plugin()
    {
        return (new \ReflectionClass(Plugin::class))->newInstanceWithoutConstructor();
    }

    /**
     * Invoke an inaccessible shipped method without changing its implementation.
     *
     * @param object|string $subject
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    protected function invoke($subject, $method, array $arguments = array())
    {
        $reflection = new \ReflectionMethod($subject, $method);
        if (PHP_VERSION_ID < 80100) {
            $reflection->setAccessible(true);
        }

        return $reflection->invokeArgs(is_object($subject) ? $subject : null, $arguments);
    }
}
