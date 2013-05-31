<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name'        => 'Low Title',
	'pi_version'     => '2.1.1',
	'pi_author'      => 'Lodewijk Schutte ~ Low',
	'pi_author_url'  => 'http://gotolow.com/software/low-title',
	'pi_description' => 'Plugin to quickly retrieve a title from an entry, category, channel or site',
	'pi_usage'       => 'See http://gotolow.com/software/low-title for more info'
);

/**
 * < EE 2.6.0 backward compat
 */
if ( ! function_exists('ee'))
{
	function ee()
	{
		static $EE;
		if ( ! $EE) $EE = get_instance();
		return $EE;
	}
}

/**
 * Low Title Plugin Class
 *
 * @package        low_title
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/software/low-title
 * @license        http://creativecommons.org/licenses/by-sa/3.0/
 */
class Low_title {

	/**
	 * Entry
	 *
	 * Get title for entry
	 *
	 * @return	string
	 */
	public function entry()
	{
		// -------------------------------------
		//  Initiate parameters and vars
		// -------------------------------------

		$params = array(
			'entry_id'     => '',
			'url_title'    => '',
			'weblog_id'    => '',
			'weblog'       => '',
			'custom_field' => '',
			'channel_id'   => '',
			'channel'      => '',
			'pages_uri'    => '',
			'show_error'   => '',
			'fallback'     => ''
		);

		$field_id = FALSE;
		$sql_select = 'title';

		// -------------------------------------
		//  Loop through parameters, set value
		// -------------------------------------

		foreach ($params AS $key => $value)
		{
			$params[$key] = ee()->TMPL->fetch_param($key);
		}

		// -------------------------------------
		//  Convert 'weblog_id' to 'channel_id'
		// -------------------------------------

		if ( !$params['channel_id'] && $params['weblog_id'] )
		{
			$params['channel_id'] = $params['weblog_id'];
		}

		// -------------------------------------
		//  Convert 'weblog' to 'channel'
		// -------------------------------------

		if ( !$params['channel'] && $params['weblog'] )
		{
			$params['channel'] = $params['weblog'];
		}

		// -------------------------------------
		//  Custom field? Get its ID
		// -------------------------------------

		if ($params['custom_field'])
		{
			ee()->db->select('field_id');
			ee()->db->from('exp_channel_fields');
			ee()->db->where('field_name', $params['custom_field']);
			$query = ee()->db->get();

			if ($query->num_rows())
			{
				$row = $query->row();
				$field_id = $row->field_id;
				$sql_select = (($params['fallback'] == 'yes') ? "IF(d.field_id_{$field_id}='',t.title,d.field_id_{$field_id})" : "d.field_id_{$field_id}") . " AS title";
			}
			else
			{
				if ($params['show_error'] == 'yes')
				{
					// Show error if no custom field was found
					$this->return_data = "Custom field '{$params['custom_field']}' not found";
					return $this->return_data;
				}
			}
		}

		// -------------------------------------
		//  Start composing query
		// -------------------------------------

		ee()->db->select($sql_select, FALSE);
		ee()->db->from('exp_channel_titles AS t');
		ee()->db->join('exp_channels AS ch', 't.channel_id = ch.channel_id');

		// extra join if needed
		if ($field_id)
		{
			ee()->db->join('exp_channel_data AS d', 'd.entry_id = t.entry_id');
		}

		// sql for entry_id
		if ($params['entry_id'])
		{
			ee()->db->where('t.entry_id', $params['entry_id']);
		}

		// sql for url_title
		if ($params['url_title'])
		{
			ee()->db->where('t.url_title', $params['url_title']);
		}

		// sql for channel_id
		if ($params['channel_id'])
		{
			ee()->db->where('t.channel_id', $params['channel_id']);
		}

		// sql for channel
		if ($params['channel'])
		{
			ee()->db->where('ch.channel_name', $params['channel']);
		}

		// sql for pages uri
		if ($params['pages_uri'])
		{
			// Normalize slashes
			// $params['pages_uri'] = '/' . trim($params['pages_uri'], '/') . '/';

			// Get all sites pages
			$pages = ee()->config->config['site_pages'];

			// Get current site_id
			$site_id = ee()->config->config['site_id'];

			// Get current site pages
			$pages = isset($pages[$site_id]) ? $pages[$site_id] : FALSE;

			// Flip id => page_uri
			if (is_array($pages) && is_array($pages['uris']))
			{
				$pages = array_flip($pages['uris']);
			}

			// Check if given uri exists, limit query by its id
			if (isset($pages[$params['pages_uri']]))
			{
				ee()->db->where('t.entry_id', $pages[$params['pages_uri']]);
			}
			else
			{
				// if uri doesn't exist, throw error
				if ($params['show_error'] == 'yes')
				{
					// Show error if no custom field was found
					$this->return_data = "Pages URI '{$params['pages_uri']}' not found";
					return $this->return_data;
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

		if ($query->num_rows())
		{
			$row = $query->row();
			$this->return_data = $row->title;
			$this->_format();
		}
		else
		{
			$this->return_data = '';
		}
		ee()->TMPL->log_item("Low Title, returning ".$this->return_data);
		return $this->return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Category
	 *
	 * Get title for category
	 *
	 * @access	public
	 * @return	string
	 */
	public function category()
	{
		// -------------------------------------
		//  Initiate parameters and vars
		// -------------------------------------

		$params = array(
			'category_id'	=> '',
			'url_title'		=> '',
			'category_group'=> '',
			'custom_field'	=> '',
			'show_error'	=> '',
			'fallback'		=> ''
		);

		$field_id = FALSE;
		$sql_select = 'cat_name';

		// -------------------------------------
		//  Loop through parameters, set value
		// -------------------------------------

		foreach ($params AS $key => $value)
		{
			$params[$key] = ee()->TMPL->fetch_param($key);
		}

		// -------------------------------------
		//  Custom field? Get its ID
		// -------------------------------------

		if ($params['custom_field'])
		{
			ee()->db->select('field_id');
			ee()->db->from('exp_category_fields');
			ee()->db->where('field_name', $params['custom_field']);
			$query = ee()->db->get();

			if ($query->num_rows())
			{
				$row = $query->row();
				$field_id = $row->field_id;
				$sql_select = (($params['fallback'] == 'yes') ? "IF(d.field_id_{$field_id}='',c.cat_name,d.field_id_{$field_id})" : "d.field_id_{$field_id}");
			}
			else
			{
				if ($params['show_error'] == 'yes')
				{
					// Show error if no custom field was found
					$this->return_data = "Custom field '{$params['custom_field']}' not found";
					return $this->return_data;
				}
			}
		}

		// -------------------------------------
		//  Start composing query
		// -------------------------------------

		ee()->db->select(($field_id ? "d.field_id_{$field_id}" : 'cat_name').' AS title', FALSE);
		ee()->db->from('exp_categories AS c');

		// extra join if needed
		if ($field_id)
		{
			ee()->db->join('exp_category_field_data AS d', 'd.cat_id = c.cat_id');
		}

		// sql for category_id
		if ($params['category_id'])
		{
			ee()->db->where('c.cat_id', $params['category_id']);
		}

		// sql for url_title
		if ($params['url_title'])
		{
			ee()->db->where('c.cat_url_title', $params['url_title']);
		}

		// sql for category_group
		if ($params['category_group'])
		{
			ee()->db->where('c.group_id', $params['category_group']);
		}

		// limit query
		ee()->db->limit(1);

		// execute query
		$query = ee()->db->get();

		// -------------------------------------
		//  Return formatted or empty string
		// -------------------------------------

		if ($query->num_rows())
		{
			$row = $query->row();
			$this->return_data = $row->title;
			$this->_format();
		}
		else
		{
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
	 * @access	public
	 * @return	string
	 */
	public function channel()
	{
		// -------------------------------------
		//  Initiate parameters and vars
		// -------------------------------------

		$params = array(
			'weblog_id'		=> '',
			'weblog_name'	=> '',
			'channel_id'	=> '',
			'channel_name'	=> ''
		);

		// -------------------------------------
		//  Loop through parameters, set value
		// -------------------------------------

		foreach ($params AS $key => $value)
		{
			$params[$key] = ee()->TMPL->fetch_param($key);
		}

		// -------------------------------------
		//  Convert 'weblog_id' to 'channel_id'
		// -------------------------------------

		if ( !$params['channel_id'] && $params['weblog_id'] )
		{
			$params['channel_id'] = $params['weblog_id'];
		}

		// -------------------------------------
		//  Convert 'weblog_name' to 'channel_name'
		// -------------------------------------

		if ( !$params['channel_name'] && $params['weblog_name'] )
		{
			$params['channel_name'] = $params['weblog_name'];
		}

		// -------------------------------------
		//  Start composing query
		// -------------------------------------

		ee()->db->select('channel_title AS title');
		ee()->db->from('exp_channels');

		// sql for channel_id
		if ($params['channel_id'])
		{
			ee()->db->where('channel_id', $params['channel_id']);
		}

		// sql for channel_name
		if ($params['channel_name'])
		{
			ee()->db->where('channel_name', $params['channel_name']);
		}

		// limit query
		ee()->db->limit(1);

		// execute query
		$query = ee()->db->get();

		// -------------------------------------
		//  Return formatted or empty string
		// -------------------------------------

		if ($query->num_rows())
		{
			$row = $query->row();
			$this->return_data = $row->title;
			$this->_format();
		}
		else
		{
			$this->return_data = '';
		}

		return $this->return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Weblog
	 *
	 * Alias for Channel
	 *
	 * @see		channel()
	 */
	public function weblog()
	{
		return $this->channel();
	}

	// --------------------------------------------------------------------

	/**
	 * Site
	 *
	 * Get title for site
	 *
	 * @access	public
	 * @return	string
	 */
	public function site()
	{
		// -------------------------------------
		//  Initiate parameters and vars
		// -------------------------------------

		$params = array(
			'site_id'	=> '',
			'site_name'	=> ''
		);

		// -------------------------------------
		//  Loop through parameters, set value
		// -------------------------------------

		foreach ($params AS $key => $value)
		{
			$params[$key] = ee()->TMPL->fetch_param($key);
		}

		// -------------------------------------
		//  Start composing query
		// -------------------------------------

		ee()->db->select('site_label AS title');
		ee()->db->from('exp_sites');

		// sql for site_id
		if ($params['site_id'])
		{
			ee()->db->where('site_id', $params['site_id']);
		}

		// sql for site_name
		if ($params['site_name'])
		{
			ee()->db->where('site_name', $params['site_name']);
		}

		// limit query
		ee()->db->limit(1);

		// execute query
		$query = ee()->db->get();

		// -------------------------------------
		//  Return formatted or empty string
		// -------------------------------------

		if ($query->num_rows())
		{
			$row = $query->row();
			$this->return_data = $row->title;
			$this->_format();
		}
		else
		{
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
	 * @access	private
	 * @return	void
	 */
	private function _format()
	{
		if ( !strlen($this->return_data) || ee()->TMPL->fetch_param('format') === 'no' ) return;

		ee()->load->library('typography');

		$this->return_data = ee()->typography->format_characters($this->return_data);
	}


	// --------------------------------------------------------------------

}
// END CLASS

/* End of file pi.low_title.php */