<?php

/**
 * Minimal EE_Fieldtype contract; deliberately supplies no dynamic-property opt-out.
 */
abstract class EE_Fieldtype
{
    public $settings = array();
    protected $content_id = null;

    /**
     * Return the content identifier exposed by ExpressionEngine's fieldtype base.
     *
     * @return int|null
     */
    public function content_id()
    {
        return $this->content_id;
    }
}
