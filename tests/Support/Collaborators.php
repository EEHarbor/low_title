<?php

namespace LowTitleTests;

use RuntimeException;

class Input
{
    public $getValues = array();
    public $postValues = array();
    public $cleaned = array();
    public $cleaner;

    /**
     * Read GET/POST values with EE's POST-first combined lookup and strict XSS flag.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws RuntimeException
     */
    public function __call($method, $arguments)
    {
        if (!in_array($method, array('get', 'post', 'get_post'), true)) {
            throw new RuntimeException('Unexpected input method ' . $method);
        }
        $name = $arguments[0];
        $get = $this->getValues;
        $post = $this->postValues;
        $data = $method === 'get' || ($method === 'get_post' && !isset($post[$name])) ? $get : $post;
        if (!isset($data[$name])) {
            return false;
        }
        $value = $data[$name];
        if (isset($arguments[1]) && $arguments[1] === true) {
            $this->cleaned[] = $value;

            return $this->cleaner ? call_user_func($this->cleaner, $value) : $value;
        }

        return $value;
    }
}

class Recorder
{
    public $calls = array();
    public $returns = array();
    public $footer_item = array();
    public $shortname = 'low_title';
    public $cache = array();

    /**
     * Record a configured collaborator method with an explicit result contract.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws RuntimeException
     */
    public function __call($method, $arguments)
    {
        $this->calls[] = array_merge(array($method), $arguments);
        if (!array_key_exists($method, $this->returns)) {
            throw new RuntimeException('Unconfigured collaborator method ' . $method);
        }

        return $this->returns[$method] === $this ? $this : $this->returns[$method];
    }
}

class Url
{
    public $path;
    public $variables;

    /**
     * Store a CP URL path and its query parameters.
     *
     * @param string $path
     * @param array $variables
     * @return void
     */
    public function __construct($path, array $variables = array())
    {
        $this->path = $path;
        $this->variables = $variables;
    }

    /**
     * Compile a deterministic URL for visible assertions.
     *
     * @return string
     */
    public function compile()
    {
        return '/cp/' . $this->path . ($this->variables ? '?' . http_build_query($this->variables) : '');
    }
}

class UrlFactory
{
    /**
     * Create the URL returned by ExpressionEngine's CP/URL service.
     *
     * @param string $path
     * @param array $variables
     * @return Url
     */
    public function make($path, array $variables = array())
    {
        return new Url($path, $variables);
    }
}

class Uri
{
    public $path = 'cp/addons/settings/low_title/index';

    /**
     * Return the current control panel URI string.
     *
     * @return string
     */
    public function uri_string()
    {
        return $this->path;
    }

    /**
     * Return the current URI segments.
     *
     * @return array
     */
    public function segment_array()
    {
        return explode('/', $this->path);
    }
}

class Alert extends Recorder
{
    /**
     * Expose a real optional alert modifier for method_exists compatibility.
     *
     * @return Alert
     */
    public function cannotClose()
    {
        $this->calls[] = array('cannotClose');

        return $this;
    }
}

class Sidebar
{
    public $items = array();

    /**
     * Create a distinct navigation header for each label.
     *
     * @param string $title
     * @return NavItem
     */
    public function addHeader($title)
    {
        $item = new NavItem;
        $item->title = $title;
        $this->items[] = $item;

        return $item;
    }
}

class NavItem
{
    public $title;
    public $url;
    public $buttons = array();
    public $active = false;

    /**
     * Bind a navigation header URL.
     *
     * @param string|Url $url
     * @return NavItem
     */
    public function withUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Add a secondary navigation action.
     *
     * @param string $label
     * @param string|Url $url
     * @return NavItem
     */
    public function withButton($label, $url)
    {
        $this->buttons[] = array($label, $url);

        return $this;
    }

    /**
     * Mark the item as the active navigation selection.
     *
     * @return NavItem
     */
    public function isActive()
    {
        $this->active = true;

        return $this;
    }
}

class ViewFactory
{
    public $template;
    public $variables;

    /**
     * Select a named template.
     *
     * @param string $template
     * @return ViewFactory
     */
    public function make($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Select an inline template.
     *
     * @param string $template
     * @return ViewFactory
     */
    public function makeFromString($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Capture variables supplied to the selected template.
     *
     * @param array $variables
     * @return string
     */
    public function render($variables)
    {
        $this->variables = $variables;

        return 'rendered:' . $this->template;
    }
}

class Redirect extends \RuntimeException
{
    public $location;

    /**
     * Represent EE's terminating redirect without terminating the test runner.
     *
     * @param mixed $location
     * @return void
     */
    public function __construct($location)
    {
        $this->location = $location;
        parent::__construct('Request redirected');
    }
}

class Functions extends Recorder
{
    /**
     * Terminate the simulated request exactly where EE redirects.
     *
     * @param mixed $location
     * @return never
     * @throws Redirect
     */
    public function redirect($location)
    {
        $this->calls[] = array('redirect', $location);

        throw new Redirect($location);
    }
}

class ModelQuery
{
    public $calls = array();
    public $total = 100;

    /**
     * Preserve fluent model-query semantics used by the pagination helper.
     *
     * @param string $method
     * @param array $arguments
     * @return ModelQuery|int
     * @throws RuntimeException
     */
    public function __call($method, $arguments)
    {
        $this->calls[] = array_merge(array($method), $arguments);
        if ($method === 'count') {
            return $this->total;
        }
        if (in_array($method, array('all', 'limit', 'offset'), true)) {
            return $this;
        }

        throw new RuntimeException('Unexpected model method ' . $method);
    }
}

class StatefulCache
{
    public $calls = array();
    public $store = array();

    /**
     * Read a persistent cache value while preserving false-like stored values.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $this->calls[] = array('get', $key);

        return array_key_exists($key, $this->store) ? $this->store[$key] : false;
    }

    /**
     * Persist a cache value and report success.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function save($key, $value)
    {
        $this->calls[] = array('save', $key, $value);
        $this->store[$key] = $value;

        return true;
    }

    /**
     * Remove the requested persistent cache value.
     *
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $this->calls[] = array('delete', $key);
        unset($this->store[$key]);

        return true;
    }
}
