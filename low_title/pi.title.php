<?php
/*
=====================================================
 This plugin was created by Lodewijk Schutte
 - freelance@loweblog.com
 - http://loweblog.com/freelance/
=====================================================
 File: pi.title.php
-----------------------------------------------------
 Purpose: Title retrieval plugin
=====================================================
*/

if ( ! defined('EXT'))
{
	exit('Invalid file request');
}

$plugin_info = array(
	'pi_name'			=> 'Title',
	'pi_version'		=> '1.4',
	'pi_author'			=> 'Lodewijk Schutte',
	'pi_author_url'		=> 'http://loweblog.com/freelance/',
	'pi_description'	=> 'Plugin to quickly retrieve a title from an entry, category, weblog or site',
	'pi_usage'			=> title::usage()
);

class Title {

	var $return_data;

	// ----------------------------------------
	//	Title me, title you
	// ----------------------------------------

	function Title()
	{
		return $this->entry();
	}

	// ----------------------------------------
	//	Weblog entry title
	// ----------------------------------------
	function entry()
	{
		global $DB, $TMPL, $PREFS, $FNS;
	
		// get some params
		$entry_id	= $TMPL->fetch_param('entry_id')		? $TMPL->fetch_param('entry_id')		: '';
		$url_title	= $TMPL->fetch_param('url_title')		? $TMPL->fetch_param('url_title')		: '';
		$weblog_id	= $TMPL->fetch_param('weblog_id')		? $TMPL->fetch_param('weblog_id')		: '';
		$weblog		= $TMPL->fetch_param('weblog')			? $TMPL->fetch_param('weblog')			: '';
		$custom		= $TMPL->fetch_param('custom_field')	? $TMPL->fetch_param('custom_field')	: '';
		$pages_uri	= $TMPL->fetch_param('pages_uri')		? $TMPL->fetch_param('pages_uri')		: '';
	
		$this->show_error = ($TMPL->fetch_param('show_error') == 'yes') ? true : false;
		$this->fallback = ($TMPL->fetch_param('fallback') == 'yes') ? true : false;
	
		// default sql values
		$sql_select	= 'title';
		$sql_from	= 'exp_weblog_titles t, exp_weblogs w';
		$sql_where	= 't.weblog_id = w.weblog_id';
	
		// sql for entry_id
		if ($entry_id)
		{
			$entry_id = "AND t.entry_id = '".$DB->escape_str($entry_id)."'";
		}
	
		// sql for url_title
		if ($url_title)
		{
			$url_title = "AND t.url_title = '".$DB->escape_str($url_title)."'";
		}
	
		// sql for weblog_id
		if ($weblog_id)
		{
			$weblog_id = "AND t.weblog_id = '".$DB->escape_str($weblog_id)."'";
		}
	
		// sql for weblog
		if ($weblog)
		{
			$weblog = "AND w.blog_name = '".$DB->escape_str($weblog)."'";
		}
		
		
		// Hat tip to Tim Kelty
		if ($pages_uri)
		{
			$pages = $PREFS->ini('site_pages');
			
			// EE1.6.9 fix
			if (isset($pages[$PREFS->ini('site_id')]))
			{
				$pages = $pages[$PREFS->ini('site_id')];
			}

			// get and clean uri
			$pages_uri = str_replace('&#47;', '/', $pages_uri);

			// find id
			$tmp = array_flip($pages['uris']);
			
			if (isset($tmp[$pages_uri]))
			{
				$entry_id = "AND t.entry_id = '".$DB->escape_str($tmp[$pages_uri])."'";
			}
			else
			{
				if ($this->show_error) // will show error if no custom field was found
				{
					$this->return_data = 'Pages uri not found';
					return $this->return_data;
				}
			}
			
			// clean up
			unset($tmp);
		}

		// sql for custom field
		if ($custom)
		{
			$res = $DB->query("SELECT CONCAT('field_id_',field_id) AS field FROM exp_weblog_fields WHERE field_name = '".$DB->escape_str($custom)."'");
			if ($res->num_rows)
			{
				$sql_select	= ($this->fallback ? "IF(d.{$res->row['field']}='',t.title,d.{$res->row['field']})" : "d.{$res->row['field']}") . " AS title";
				$sql_from	.= ', exp_weblog_data d';
				$sql_where	.= ' AND d.entry_id = t.entry_id';
			}
			else
			{
				if ($this->show_error) // will show error if no custom field was found
				{
					$this->return_data = 'Custom field not found';
					return $this->return_data;
				}
			}
		}
				
		// start building query
		$sql = "
			SELECT
				{$sql_select}
			FROM
				{$sql_from}
			WHERE
				{$sql_where}
				{$entry_id}
				{$url_title}
				{$weblog_id}
				{$weblog}
			LIMIT 1
		";
	
		// execute query
		$query = $DB->query($sql);
	
		$this->return_data = ($query->num_rows > 0) ? $query->row['title'] : '';
		$this->_format();
		return $this->return_data;
	}
	// END entry()

	// ----------------------------------------
	//	Category title
	// ----------------------------------------
	function category()
	{
		global $DB, $TMPL;
	
		// get some params
		$cat_id		= $TMPL->fetch_param('category_id')	 	? $TMPL->fetch_param('category_id')	 	: '';
		$url_title	= $TMPL->fetch_param('url_title')		? $TMPL->fetch_param('url_title')		: '';
		$group_id	= $TMPL->fetch_param('category_group')	? $TMPL->fetch_param('category_group')	: '';
		$custom		= $TMPL->fetch_param('custom_field')	? $TMPL->fetch_param('custom_field')	: '';
	
		$this->show_error = ($TMPL->fetch_param('show_error') == 'yes') ? true : false;
		$this->fallback = ($TMPL->fetch_param('fallback') == 'yes') ? true : false;	
		
		// default sql values
		$sql_select = 'cat_name AS title';
		$sql_from	 = 'exp_categories c';
		$sql_where	= '1';
	
		// sql for cat_id
		if ($cat_id)
		{
			// strip C from id if it's there
			$cat_id = str_replace('C','',$cat_id);
			$cat_id = "AND c.cat_id = '".$DB->escape_str($cat_id)."'";
		}
	
		// sql for url_title
		if ($url_title)
		{
			$url_title = "AND c.cat_url_title = '".$DB->escape_str($url_title)."'";
		}
	
		// sql for group_id
		if ($group_id)
		{
			$group_id = "AND c.group_id = '".$DB->escape_str($group_id)."'";
		}
	
		// sql for custom field
		if ($custom)
		{
			$res = $DB->query("SELECT CONCAT('field_id_',field_id) AS field FROM exp_category_fields WHERE field_name = '".$DB->escape_str($custom)."'");
			if ($res->num_rows)
			{
				$sql_select = ($this->fallback ? "IF(d.{$res->row['field']}='',c.cat_name,d.{$res->row['field']})" : "d.{$res->row['field']}") . " AS title";
				$sql_from  .= ', exp_category_field_data d';
				$sql_where .= ' AND d.cat_id = c.cat_id';
			}
			else
			{
				if ($this->show_error) // will show error if no custom field was found
				{
					$this->return_data = 'Custom field not found';
					return $this->return_data;
				}
			}
		}
				
		// start building query
		$sql = "
			SELECT
				{$sql_select}
			FROM
				{$sql_from}
			WHERE
				{$sql_where}
				{$cat_id}
				{$url_title}
				{$group_id}
			LIMIT 1
		";
		// execute query
		$query = $DB->query($sql);
	
		$this->return_data = ($query->num_rows > 0) ? $query->row['title'] : '';
		$this->_format();
		return $this->return_data;
	}
	// END category()

	// ----------------------------------------
	//	Weblog title
	// ----------------------------------------
	function weblog()
	{
		global $DB, $TMPL;
	
		// get some params
		$weblog_id	 = $TMPL->fetch_param('weblog_id')		? $TMPL->fetch_param('weblog_id')	: '';
		$weblog_name = $TMPL->fetch_param('weblog_name')	? $TMPL->fetch_param('weblog_name')	: '';
	
		// default sql values
		$sql_select = 'blog_title AS title';
		$sql_from	= 'exp_weblogs';
		$sql_where	= '1';
	
		// sql for weblog_id
		if ($weblog_id)
		{
			$weblog_id = "AND weblog_id = '".$DB->escape_str($weblog_id)."'";
		}
	
		// sql for blog_name
		if ($weblog_name)
		{
			$weblog_name = "AND blog_name = '".$DB->escape_str($weblog_name)."'";
		}
	
		// start building query
		$sql = "
			SELECT
				{$sql_select}
			FROM
				{$sql_from}
			WHERE
				{$sql_where}
				{$weblog_id}
				{$weblog_name}
			LIMIT 1
		";
		// execute query
		$query = $DB->query($sql);
	
		$this->return_data = ($query->num_rows > 0) ? $query->row['title'] : '';
		$this->_format();
		return $this->return_data;
	}
	// END weblog()

	// ----------------------------------------
	//	Site title
	// ----------------------------------------
	function site()
	{
		global $DB, $TMPL;
	
		// get some params
		$site_id	= $TMPL->fetch_param('site_id')		? $TMPL->fetch_param('site_id')		: '';
		$site_name	= $TMPL->fetch_param('site_name')	? $TMPL->fetch_param('site_name')	: '';
	
		// default sql values
		$sql_select = 'site_label AS title';
		$sql_from	= 'exp_sites';
		$sql_where	= '1';
	
		// sql for weblog_id
		if ($site_id)
		{
			$site_id = "AND site_id = '".$DB->escape_str($site_id)."'";
		}
	
		// sql for blog_name
		if ($site_name)
		{
			$site_name = "AND site_name = '".$DB->escape_str($site_name)."'";
		}
	
		// start building query
		$sql = "
			SELECT
				{$sql_select}
			FROM
				{$sql_from}
			WHERE
				{$sql_where}
				{$site_id}
				{$site_name}
			LIMIT 1
		";
		// execute query
		$query = $DB->query($sql);
	
		$this->return_data = ($query->num_rows > 0) ? $query->row['title'] : '';
		$this->_format();
		return $this->return_data;
	}
	// END site()

	// ----------------------------------------
	//	Format found title
	// ----------------------------------------
	function _format()
	{
		if (!strlen($this->return_data)) return;
	
		if (!class_exists('Typography'))
		{
			require PATH_CORE.'core.typography'.EXT;
		}
	
		$TYPE = new Typography;
	
		$this->return_data = $TYPE->light_xhtml_typography($this->return_data);
	}



	// ----------------------------------------
	//	Plugin Usage
	// ----------------------------------------
	function usage()
	{
		ob_start(); 
?>
Some examples:

{exp:title:entry entry_id="15"}
{exp:title:entry url_title="{segment_2}" weblog="default_site"}
{exp:title:entry url_title="{segment_3}" custom_field="title_{language}"}
{exp:title:entry url_title="{segment_3}" custom_field="title_{language}" fallback="yes"}

{exp:title:category category_id="18"}
{exp:title:category category_id="C24"}
{exp:title:category url_title="{segment_4}" category_group="1"}
{exp:title:category url_title="{segment_3}" custom_field="title_{language}"}
{exp:title:category url_title="{segment_3}" custom_field="title_{language}" fallback="yes"}

{exp:title:weblog weblog_id="3"}
{exp:title:weblog weblog_name="{segment_1}"}

{exp:title:site site_id="1"}
{exp:title:site site_name="{segment_1}"}

<?php

		$buffer = ob_get_contents();
		ob_end_clean(); 

		return $buffer;
	}
	// END

}
// END CLASS
?>