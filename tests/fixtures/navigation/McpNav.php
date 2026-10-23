<?php

namespace Low\Title\Conduit;

class McpNav extends \Low\Title\FluxCapacitor\Conduit\McpNav
{
    public $flux;
}

class ConfiguredNav extends McpNav
{
    public $events = array();

    /**
     * Supply the extension's configured navigation items.
     *
     * @return array|false|null
     */
    protected function defaultItems()
    {
        return $GLOBALS['low_title_nav_options']['items'];
    }

    /**
     * Supply secondary actions through the original extension hook.
     *
     * @return array|false|null
     */
    protected function defaultButtons()
    {
        return $GLOBALS['low_title_nav_options']['buttons'];
    }

    /**
     * Supply controller aliases through the original extension hook.
     *
     * @return array|false|null
     */
    protected function defaultActiveMap()
    {
        return $GLOBALS['low_title_nav_options']['map'];
    }

    /**
     * Permit explicit deferral until the controller is ready.
     *
     * @return bool
     */
    public function deferGenerate()
    {
        return $GLOBALS['low_title_nav_options']['defer'];
    }

    /**
     * Observe the supported hook before sidebar creation.
     *
     * @return void
     */
    public function preGenerateNav()
    {
        $this->events[] = array('pre', isset($this->sidebar));
    }

    /**
     * Observe the supported hook after completed generation.
     *
     * @return void
     */
    public function postGenerateNav()
    {
        $this->events[] = array('post', isset($this->sidebar));
    }
}

class NativeNavWithoutProperties extends \Low\Title\FluxCapacitor\Conduit\McpNav
{
}
