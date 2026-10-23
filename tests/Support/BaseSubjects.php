<?php

namespace LowTitleTests;

class ExtensionSubject extends \Low\Title\FluxCapacitor\Base\Ext
{
    public $version = '1.2.3';
    public $name;
    public $description;
    public $docs_url;
}

class ModuleSubject extends \Low\Title\FluxCapacitor\Base\Mod
{
    public $version;
}

class TabSubject extends \Low\Title\FluxCapacitor\Base\Tab
{
    public $version;
}

class FieldtypeSubject extends \Low\Title\FluxCapacitor\Base\Ft
{
    public $version;
    public $GlobalFieldSettings;

    /**
     * Set a content identifier through the same protected storage as EE_Fieldtype.
     *
     * @param int|null $value
     * @return void
     */
    public function setContentId($value)
    {
        $this->content_id = $value;
    }
}

class UpdaterSubject extends \Low\Title\FluxCapacitor\Base\Upd
{
    public $version = '1.2.3';
    public $module_name = 'Low_title';
}

class McpSubject extends \Low\Title\FluxCapacitor\Base\Mcp
{
    public $version;
    public $nav;
    public $data = array();
}

class FieldSettingsSubject extends \Low\Title\FluxCapacitor\Conduit\GlobalFieldSettings
{
    public $flux;
    public $fieldtype_name;
}

class VersionSubject extends \Low\Title\FluxCapacitor\Conduit\Version
{
    public $flux;
}
