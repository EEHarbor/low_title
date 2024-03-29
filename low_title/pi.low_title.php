<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Low Title Plugin Class
 *
 * @package        low_title
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-title
 * @license        http://creativecommons.org/licenses/by-sa/3.0/
 */

include_once "addon.setup.php";
use Low\Title\FluxCapacitor\Base\Pi;

class Low_title extends Pi
{

    /**
     * Entry
     *
     * Get title for entry
     *
     * @return  string
     */
    public function entry()
    {
        // -------------------------------------
        //  Initiate parameters and vars
        // -------------------------------------

        $params = array(
            'entry_id'     => '',
            'url_title'    => '',
            'custom_field' => '',
            'channel_id'   => '',
            'channel'      => '',
            'pages_uri'    => '',
            'show_error'   => '',
            'fallback'     => ''
        );

        $field_id = false;
        $sql_select = 'title';

        // -------------------------------------
        //  Loop through parameters, set value
        // -------------------------------------

        foreach ($params as $key => $value) {
            $params[$key] = ee()->TMPL->fetch_param($key);
        }

        // -------------------------------------
        //  Custom field? Get its ID
        // -------------------------------------

        if ($params['custom_field']) {
            if ($field_id = $this->_get_channel_field_id($params['custom_field'])) {
                $sql_select = (($params['fallback'] == 'yes')
                    ? "IF(d.field_id_{$field_id}='',t.title,d.field_id_{$field_id})"
                    : "d.field_id_{$field_id}") . " AS title";
            } else {
                if ($params['show_error'] == 'yes') {
                    // Show error if no custom field was found
                    return "Custom field '{$params['custom_field']}' not found";
                }
            }
        }

        // -------------------------------------
        //  Start composing query
        // -------------------------------------

        ee()->db->select($sql_select, false);
        ee()->db->from('exp_channel_titles AS t');
        ee()->db->join('exp_channels AS ch', 't.channel_id = ch.channel_id');

        // extra join if needed
        if ($field_id) {
            ee()->db->join('exp_channel_data AS d', 'd.entry_id = t.entry_id');
        }

        // sql for entry_id
        if ($params['entry_id']) {
            ee()->db->where('t.entry_id', $params['entry_id']);
        }

        // sql for url_title
        if ($params['url_title']) {
            ee()->db->where('t.url_title', $params['url_title']);
        }

        // sql for channel_id
        if ($params['channel_id']) {
            ee()->db->where('t.channel_id', $params['channel_id']);
        }

        // sql for channel
        if ($params['channel']) {
            ee()->db->where('ch.channel_name', $params['channel']);
        }

        // sql for pages uri
        if ($params['pages_uri']) {
            // Normalize slashes
            // $params['pages_uri'] = '/' . trim($params['pages_uri'], '/') . '/';

            // Get all sites pages
            $pages = ee()->config->config['site_pages'];

            // Get current site_id
            $site_id = ee()->config->config['site_id'];

            // Get current site pages
            $pages = isset($pages[$site_id]) ? $pages[$site_id] : false;

            // Flip id => page_uri
            if (is_array($pages) && is_array($pages['uris'])) {
                $pages = array_flip($pages['uris']);
            }

            // Check if given uri exists, limit query by its id
            if (isset($pages[$params['pages_uri']])) {
                ee()->db->where('t.entry_id', $pages[$params['pages_uri']]);
            } else {
                // if uri doesn't exist, throw error
                if ($params['show_error'] == 'yes') {
                    // Show error if no custom field was found
                    return "Pages URI '{$params['pages_uri']}' not found";
                }
            }
        }

        // limit query
        ee()->db->limit(1);

        // execute query
        $query = ee()->db->get();

        // -------------------------------------
        //  Return formatted or empty string
        // -------------------------------------

        if ($query->num_rows()) {
            $row = $query->row();
            $this->return_data = $row->title;
            $this->_format();
        } else {
            $this->return_data = '';
        }

        ee()->TMPL->log_item("Low Title, returning " . $this->return_data);

        return $this->return_data;
    }

    // --------------------------------------------------------------------

    /**
     * Category
     *
     * Get title for category
     *
     * @access  public
     * @return  string
     */
    public function category()
    {
        // -------------------------------------
        //  Initiate parameters and vars
        // -------------------------------------

        $params = array(
            'category_id'   => '',
            'url_title'     => '',
            'category_group' => '',
            'custom_field'  => '',
            'show_error'    => '',
            'fallback'      => ''
        );

        $field_id = false;
        $sql_select = 'cat_name';

        // -------------------------------------
        //  Loop through parameters, set value
        // -------------------------------------

        foreach ($params as $key => $value) {
            $params[$key] = ee()->TMPL->fetch_param($key);
        }

        // -------------------------------------
        //  Custom field? Get its ID
        // -------------------------------------

        if ($params['custom_field']) {
            if ($field_id = $this->_get_category_field_id($params['custom_field'])) {
                $sql_select = (($params['fallback'] == 'yes')
                    ? "IF(d.field_id_{$field_id}='',c.cat_name,d.field_id_{$field_id})"
                    : "d.field_id_{$field_id}");
            } else {
                if ($params['show_error'] == 'yes') {
                    // Show error if no custom field was found
                    $this->return_data = "Custom field '{$params['custom_field']}' not found";
                    return $this->return_data;
                }
            }
        }

        // -------------------------------------
        //  Start composing query
        // -------------------------------------

        ee()->db->select($sql_select . ' AS title', false);
        ee()->db->from('exp_categories AS c');

        // extra join if needed
        if ($field_id) {
            ee()->db->join('exp_category_field_data AS d', 'd.cat_id = c.cat_id');
        }

        // sql for category_id
        if ($params['category_id']) {
            ee()->db->where('c.cat_id', ltrim($params['category_id'], 'C'));
        }

        // sql for url_title
        if ($params['url_title']) {
            ee()->db->where('c.cat_url_title', $params['url_title']);
        }

        // sql for category_group
        if ($params['category_group']) {
            ee()->db->where('c.group_id', $params['category_group']);
        }

        // limit query
        ee()->db->limit(1);

        // execute query
        $query = ee()->db->get();

        // -------------------------------------
        //  Return formatted or empty string
        // -------------------------------------

        if ($query->num_rows()) {
            $row = $query->row();
            $this->return_data = $row->title;
            $this->_format();
        } else {
            $this->return_data = '';
        }

        return $this->return_data;
    }

    // --------------------------------------------------------------------

    /**
     * Channel
     *
     * Get title for channel
     *
     * @access  public
     * @return  string
     */
    public function channel()
    {
        // -------------------------------------
        //  Initiate parameters and vars
        // -------------------------------------

        $params = array(
            'channel_id'    => '',
            'channel_name'  => ''
        );

        // -------------------------------------
        //  Loop through parameters, set value
        // -------------------------------------

        foreach ($params as $key => $value) {
            $params[$key] = ee()->TMPL->fetch_param($key);
        }

        // -------------------------------------
        //  Start composing query
        // -------------------------------------

        ee()->db->select('channel_title AS title');
        ee()->db->from('exp_channels');

        // sql for channel_id
        if ($params['channel_id']) {
            ee()->db->where('channel_id', $params['channel_id']);
        }

        // sql for channel_name
        if ($params['channel_name']) {
            ee()->db->where('channel_name', $params['channel_name']);
        }

        // limit query
        ee()->db->limit(1);

        // execute query
        $query = ee()->db->get();

        // -------------------------------------
        //  Return formatted or empty string
        // -------------------------------------

        if ($query->num_rows()) {
            $row = $query->row();
            $this->return_data = $row->title;
            $this->_format();
        } else {
            $this->return_data = '';
        }

        return $this->return_data;
    }

    // --------------------------------------------------------------------

    /**
     * Site
     *
     * Get title for site
     *
     * @access  public
     * @return  string
     */
    public function site()
    {
        // -------------------------------------
        //  Initiate parameters and vars
        // -------------------------------------

        $params = array(
            'site_id'   => '',
            'site_name' => ''
        );

        // -------------------------------------
        //  Loop through parameters, set value
        // -------------------------------------

        foreach ($params as $key => $value) {
            $params[$key] = ee()->TMPL->fetch_param($key);
        }

        // -------------------------------------
        //  Start composing query
        // -------------------------------------

        ee()->db->select('site_label AS title');
        ee()->db->from('exp_sites');

        // sql for site_id
        if ($params['site_id']) {
            ee()->db->where('site_id', $params['site_id']);
        }

        // sql for site_name
        if ($params['site_name']) {
            ee()->db->where('site_name', $params['site_name']);
        }

        // limit query
        ee()->db->limit(1);

        // execute query
        $query = ee()->db->get();

        // -------------------------------------
        //  Return formatted or empty string
        // -------------------------------------

        if ($query->num_rows()) {
            $row = $query->row();
            $this->return_data = $row->title;
            $this->_format();
        } else {
            $this->return_data = '';
        }

        return $this->return_data;
    }

    // --------------------------------------------------------------------

    /**
     * Format
     *
     * Format return data
     *
     * @access  private
     * @return  void
     */
    private function _format()
    {
        if (!strlen($this->return_data) || ee()->TMPL->fetch_param('format') === 'no') {
            return;
        }

        ee()->load->library('typography');

        $this->return_data = ee()->typography->format_characters($this->return_data);
    }

    // --------------------------------------------------------------------


    /**
     * Get custom channel field id
     *
     * @access  private
     * @return  void
     */
    private function _get_channel_field_id($name)
    {
        static $map = array();

        if (! array_key_exists($name, $map)) {
            $query = ee()->db->select('field_id')
                   ->from('channel_fields')
                   ->where('field_name', $name)
                   ->get();

            $map[$name] = $query->row('field_id');
        }

        return $map[$name];
    }

    /**
     * Get custom category field id
     *
     * @access  private
     * @return  void
     */
    private function _get_category_field_id($name)
    {
        static $map = array();

        if (! array_key_exists($name, $map)) {
            $query = ee()->db->select('field_id')
                   ->from('category_fields')
                   ->where('field_name', $name)
                   ->get();

            $map[$name] = $query->row('field_id');
        }

        return $map[$name];
    }
}
// END CLASS

/* End of file pi.low_title.php */
