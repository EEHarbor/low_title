<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name'			=> 'Low Title',
	'pi_version'		=> '2.0.1',
	'pi_author'			=> 'Lodewijk Schutte ~ Low',
	'pi_author_url'		=> 'http://gotolow.com/software/low-title',
	'pi_description'	=> 'Plugin to quickly retrieve a title from an entry, category, channel or site',
	'pi_usage'			=> Low_title::usage()
);

/**
* Low Title Plugin Class
*
* @package			low-title-ee2_addon
* @version			2.0.1
* @author			Lodewijk Schutte ~ Low <hi@gotolow.com>
* @link				http://gotolow.com/software/low-title
* @license			http://creativecommons.org/licenses/by-sa/3.0/
*/
class Low_title {

	/**
	* Plugin return data
	*
	* @var	string
	*/
	var $return_data;

	// --------------------------------------------------------------------

	/**
	* PHP4 Constructor
	*
	* @see	__construct()
	*/
	function Low_title()
	{
		$this->__construct();
	}

	// --------------------------------------------------------------------

	/**
	* PHP5 Constructor
	*/
	function __construct()
	{
		/** -------------------------------------
		/**  Get global instance
		/** -------------------------------------*/

		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	* Entry
	*
	* Get title for entry
	*
	* @return	string
	*/
	function entry()
	{
		/** -------------------------------------
		/**  Initiate parameters and vars
		/** -------------------------------------*/
		
		$params = array(
			'entry_id'		=> '',
			'url_title'		=> '',
			'weblog_id'		=> '',
			'weblog'		=> '',
			'custom_field'	=> '',
			'channel_id'	=> '',
			'channel'		=> '',
			'pages_uri'		=> '',
			'show_error'	=> ''
		);
		
		$field_id = FALSE;

		/** -------------------------------------
		/**  Loop through parameters, set value
		/** -------------------------------------*/

		foreach ($params AS $key => $value)
		{
			$params[$key] = $this->EE->TMPL->fetch_param($key);
		}

		/** -------------------------------------
		/**  Convert 'weblog_id' to 'channel_id'
		/** -------------------------------------*/

		if ( !$params['channel_id'] && $params['weblog_id'] )
		{
			$params['channel_id'] = $params['weblog_id'];
		}

		/** -------------------------------------
		/**  Convert 'weblog' to 'channel'
		/** -------------------------------------*/

		if ( !$params['channel'] && $params['weblog'] )
		{
			$params['channel'] = $params['weblog'];
		}
		
		/** -------------------------------------
		/**  Custom field? Get its ID
		/** -------------------------------------*/
		
		if ($params['custom_field'])
		{
			$this->EE->db->select('field_id');
			$this->EE->db->from('exp_channel_fields');
			$this->EE->db->where('field_name', $params['custom_field']);
			$query = $this->EE->db->get();

			if ($query->num_rows())
			{
				$row = $query->row();
				$field_id = $row->field_id;
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
		
		/** -------------------------------------
		/**  Start composing query
		/** -------------------------------------*/
		
		$this->EE->db->select(($field_id ? "d.field_id_{$field_id} AS title" : 'title'));
		$this->EE->db->from('exp_channel_titles AS t');
		$this->EE->db->join('exp_channels AS ch', 't.channel_id = ch.channel_id');

		// extra join if needed
		if ($field_id)
		{
			$this->EE->db->join('exp_channel_data AS d', 'd.entry_id = t.entry_id');
		}
		
		// sql for entry_id
		if ($params['entry_id'])
		{
			$this->EE->db->where('t.entry_id', $params['entry_id']);
		}
			
		// sql for url_title
		if ($params['url_title'])
		{
			$this->EE->db->where('t.url_title', $params['url_title']);
		}
		
		// sql for channel_id
		if ($params['channel_id'])
		{
			$this->EE->db->where('t.channel_id', $params['channel_id']);
		}
		
		// sql for channel
		if ($params['channel'])
		{
			$this->EE->db->where('ch.channel_name', $params['channel']);
		}

		// sql for pages uri
		if ($params['pages_uri'])
		{
			// Normalize slashes
			// $params['pages_uri'] = '/' . trim($params['pages_uri'], '/') . '/';

			// Get all sites pages
			$pages = $this->EE->config->config['site_pages'];

			// Get current site_id
			$site_id = $this->EE->config->config['site_id'];

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
				$this->EE->db->where('t.entry_id', $pages[$params['pages_uri']]);
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
		$this->EE->db->limit(1);
		
		// execute query
		$query = $this->EE->db->get();
		
		/** -------------------------------------
		/**  Return formatted or empty string
		/** -------------------------------------*/
		
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
	* Category
	*
	* Get title for category
	*
	* @access	public
	* @return	string
	*/
	function category()
	{
		/** -------------------------------------
		/**  Initiate parameters and vars
		/** -------------------------------------*/

		$params = array(
			'category_id'	=> '',
			'url_title'		=> '',
			'category_group'=> '',
			'custom_field'	=> '',
			'show_error'	=> ''
		);
		
		$field_id = FALSE;

		/** -------------------------------------
		/**  Loop through parameters, set value
		/** -------------------------------------*/

		foreach ($params AS $key => $value)
		{
			$params[$key] = $this->EE->TMPL->fetch_param($key);
		}
		
		/** -------------------------------------
		/**  Custom field? Get its ID
		/** -------------------------------------*/
		
		if ($params['custom_field'])
		{
			$this->EE->db->select('field_id');
			$this->EE->db->from('exp_category_fields');
			$this->EE->db->where('field_name', $params['custom_field']);
			$query = $this->EE->db->get();

			if ($query->num_rows())
			{
				$row = $query->row();
				$field_id = $row->field_id;
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
		
		/** -------------------------------------
		/**  Start composing query
		/** -------------------------------------*/
		
		$this->EE->db->select(($field_id ? "d.field_id_{$field_id}" : 'cat_name').' AS title');
		$this->EE->db->from('exp_categories AS c');

		// extra join if needed
		if ($field_id)
		{
			$this->EE->db->join('exp_category_field_data AS d', 'd.cat_id = c.cat_id');
		}
		
		// sql for category_id
		if ($params['category_id'])
		{
			$this->EE->db->where('c.cat_id', $params['category_id']);
		}
			
		// sql for url_title
		if ($params['url_title'])
		{
			$this->EE->db->where('c.cat_url_title', $params['url_title']);
		}
		
		// sql for category_group
		if ($params['category_group'])
		{
			$this->EE->db->where('c.group_id', $params['category_group']);
		}

		// limit query
		$this->EE->db->limit(1);
		
		// execute query
		$query = $this->EE->db->get();
		
		/** -------------------------------------
		/**  Return formatted or empty string
		/** -------------------------------------*/
		
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
	function channel()
	{
		/** -------------------------------------
		/**  Initiate parameters and vars
		/** -------------------------------------*/
		
		$params = array(
			'weblog_id'		=> '',
			'weblog_name'	=> '',
			'channel_id'	=> '',
			'channel_name'	=> ''
		);

		/** -------------------------------------
		/**  Loop through parameters, set value
		/** -------------------------------------*/

		foreach ($params AS $key => $value)
		{
			$params[$key] = $this->EE->TMPL->fetch_param($key);
		}

		/** -------------------------------------
		/**  Convert 'weblog_id' to 'channel_id'
		/** -------------------------------------*/

		if ( !$params['channel_id'] && $params['weblog_id'] )
		{
			$params['channel_id'] = $params['weblog_id'];
		}

		/** -------------------------------------
		/**  Convert 'weblog_name' to 'channel_name'
		/** -------------------------------------*/

		if ( !$params['channel_name'] && $params['weblog_name'] )
		{
			$params['channel_name'] = $params['weblog_name'];
		}

		/** -------------------------------------
		/**  Start composing query
		/** -------------------------------------*/
		
		$this->EE->db->select('channel_title AS title');
		$this->EE->db->from('exp_channels');

		// sql for channel_id
		if ($params['channel_id'])
		{
			$this->EE->db->where('channel_id', $params['channel_id']);
		}
		
		// sql for channel_name
		if ($params['channel_name'])
		{
			$this->EE->db->where('channel_name', $params['channel_name']);
		}

		// limit query
		$this->EE->db->limit(1);
		
		// execute query
		$query = $this->EE->db->get();
		
		/** -------------------------------------
		/**  Return formatted or empty string
		/** -------------------------------------*/
		
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
	function weblog()
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
	function site()
	{
		/** -------------------------------------
		/**  Initiate parameters and vars
		/** -------------------------------------*/
		
		$params = array(
			'site_id'	=> '',
			'site_name'	=> ''
		);		

		/** -------------------------------------
		/**  Loop through parameters, set value
		/** -------------------------------------*/

		foreach ($params AS $key => $value)
		{
			$params[$key] = $this->EE->TMPL->fetch_param($key);
		}

		/** -------------------------------------
		/**  Start composing query
		/** -------------------------------------*/
		
		$this->EE->db->select('site_label AS title');
		$this->EE->db->from('exp_sites');

		// sql for site_id
		if ($params['site_id'])
		{
			$this->EE->db->where('site_id', $params['site_id']);
		}
		
		// sql for site_name
		if ($params['site_name'])
		{
			$this->EE->db->where('site_name', $params['site_name']);
		}

		// limit query
		$this->EE->db->limit(1);
		
		// execute query
		$query = $this->EE->db->get();
		
		/** -------------------------------------
		/**  Return formatted or empty string
		/** -------------------------------------*/
		
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
	function _format()
	{
		if ( !strlen($this->return_data) || $this->EE->TMPL->fetch_param('format') === 'no' ) return;
		
		$this->EE->load->library('typography');
 		
		$this->return_data = $this->EE->typography->format_characters($this->return_data);
	}
		
	// --------------------------------------------------------------------
	
	/**
	* Usage
	*
	* Plugin Usage
	*
	* @access	public
	* @return	string
	*/
	function usage()
	{
		ob_start(); 
		?>
			Some examples:

			{exp:low_title:entry entry_id="15" format="no"}
			{exp:low_title:entry pages_uri="/{segment_1}/"}
			{exp:low_title:entry url_title="{segment_2}" channel="default_site"}
			{exp:low_title:entry url_title="{segment_3}" custom_field="title_{language}"}

			{exp:low_title:category category_id="18"}
			{exp:low_title:category category_id="C24"}
			{exp:low_title:category url_title="{segment_4}" category_group="1"}
			{exp:low_title:category url_title="{segment_3}" custom_field="title_{language}"}

			{exp:low_title:channel weblog_id="3"}
			{exp:low_title:channel weblog_name="{segment_1}" format="no"}

			{exp:low_title:site site_id="1"}
			{exp:low_title:site site_name="{segment_1}"}
		<?php
		$buffer = ob_get_contents();
	
		ob_end_clean(); 

		return $buffer;
	}

	// --------------------------------------------------------------------

}
// END CLASS

/* End of file pi.low_title.php */