<?php

namespace LowTitleTests;

class NavigationUrl extends Url
{
    public $requestedUri;

    /**
     * Match the requested CP route using EE's exact path comparison.
     *
     * @return bool
     */
    public function isTheRequestedURI()
    {
        return 'cp/' . $this->path == $this->requestedUri;
    }
}

class NavigationUrlFactory
{
    private $requestedUri;

    /**
     * Capture the request URI once when the service is constructed.
     *
     * @param string $requestedUri
     * @return void
     */
    public function __construct($requestedUri)
    {
        $this->requestedUri = $requestedUri;
    }

    /**
     * Return an independent route object tied to the captured request URI.
     *
     * @param string $path
     * @param array $variables
     * @return NavigationUrl
     */
    public function make($path, array $variables = array())
    {
        $url = new NavigationUrl($path, $variables);
        $url->requestedUri = $this->requestedUri;

        return $url;
    }
}

class NavigationHeader
{
    public $text;
    public $url;
    public $button;
    public $class = '';

    /**
     * Bind a header URL and preserve native requested-route activation.
     *
     * @param string|NavigationUrl $url
     * @return NavigationHeader
     */
    public function withUrl($url)
    {
        $this->url = $url;
        if ($url instanceof NavigationUrl && $url->isTheRequestedURI()) {
            $this->isActive();
        }

        return $this;
    }

    /**
     * Retain the final secondary action installed on this header.
     *
     * @param string $text
     * @param string|NavigationUrl $url
     * @return NavigationHeader
     */
    public function withButton($text, $url)
    {
        $this->button = array('text' => $text, 'url' => $url);

        return $this;
    }

    /**
     * Append the native active class for each explicit activation.
     *
     * @return NavigationHeader
     */
    public function isActive()
    {
        $this->class .= 'active ';

        return $this;
    }
}

class NavigationSidebar
{
    public $items = array();

    /**
     * Return the sidebar service selected for the current context.
     *
     * @return NavigationSidebar
     */
    public function make()
    {
        return $this;
    }

    /**
     * Retain each distinct header in insertion order.
     *
     * @param string $text
     * @return NavigationHeader
     */
    public function addHeader($text)
    {
        $header = new NavigationHeader;
        $header->text = $text;
        $this->items[] = $header;

        return $header;
    }
}

class NavigationControlPanel
{
    public $breadcrumbs = array();
    public $rightNav;

    /**
     * Record breadcrumbs and update the compiled URL map used by views.
     *
     * @param string|NavigationUrl $url
     * @param string $title
     * @return void
     */
    public function set_breadcrumb($url, $title)
    {
        $this->breadcrumbs[] = array($url, $title);
        $key = is_object($url) ? $url->compile() : $url;
        ee()->view->cp_breadcrumbs[$key] = $title;
    }

    /**
     * Preserve the legacy controller's right-hand navigation assignment.
     *
     * @param array $items
     * @return void
     */
    public function set_right_nav($items)
    {
        $this->rightNav = $items;
    }
}
