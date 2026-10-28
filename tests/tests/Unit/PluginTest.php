<?php

namespace LowTitleTests;

class PluginTest extends TestCase
{
    /**
     * Return an empty entry title on the actual add-on class.
     *
     * @return void
     */
    public function testActualPluginReturnsAnEmptyEntryTitle()
    {
        $this->environment->db->results = array(array());
        // Bypass inherited startup to isolate the add-on-owned query.
        $plugin = (new \ReflectionClass(\Low_title::class))->newInstanceWithoutConstructor();

        $this->assertSame('', $plugin->entry());
        $this->assertSame('', $plugin->return_data);
    }

    /**
     * Return every public query tag and its fixed query prefix.
     *
     * @return array
     */
    public static function queryTags()
    {
        return array(
            'entry' => array('entry', array(array('select', 'title', false), array('from', 'exp_channel_titles AS t'), array('join', 'exp_channels AS ch', 't.channel_id = ch.channel_id'))),
            'category' => array('category', array(array('select', 'cat_name AS title', false), array('from', 'exp_categories AS c'))),
            'channel' => array('channel', array(array('select', 'channel_title AS title'), array('from', 'exp_channels'))),
            'site' => array('site', array(array('select', 'site_label AS title'), array('from', 'exp_sites'))),
        );
    }

    /**
     * Build a bounded title query and clear a stale return value when empty.
     *
     * @dataProvider queryTags
     * @param string $tag
     * @param array $prefix
     * @return void
     */
    public function testEmptyResultsResetReturnData($tag, array $prefix)
    {
        $this->environment->db->results = array(array());
        $plugin = $this->plugin();
        $plugin->return_data = 'stale';

        $this->assertSame('', $plugin->$tag());
        $this->assertSame('', $plugin->return_data);
        $this->assertSame(array_merge($prefix, array(array('limit', 1), array('get'))), $this->environment->db->calls);
        $this->assertSame(array(), $this->environment->load->libraries);
        $this->assertSame($tag === 'entry' ? array('Low Title, returning ') : array(), $this->environment->TMPL->log);
    }

    /**
     * Return title and format vectors, including empty and numeric strings.
     *
     * @return array
     */
    public static function titles()
    {
        $vectors = array();
        foreach (array('entry', 'category', 'channel', 'site') as $tag) {
            foreach (array('', '0', 'plain title', '"quoted" & title', 'é日本語') as $title) {
                foreach (array(false, 'no', 'yes') as $format) {
                    $vectors[] = array($tag, $title, $format);
                }
            }
        }

        return $vectors;
    }

    /**
     * Apply formatting only to nonempty titles when it is enabled.
     *
     * @dataProvider titles
     * @param string $tag
     * @param string $title
     * @param string|false $format
     * @return void
     */
    public function testTitleFormatting($tag, $title, $format)
    {
        $this->environment->TMPL->tagparams = array('format' => $format);
        $this->environment->db->results = array(array(array('title' => $title)));
        $expected = $title !== '' && $format !== 'no' ? 'formatted:' . $title : $title;

        $this->assertSame($expected, $this->plugin()->$tag());
        $this->assertSame($expected === $title ? array() : array('typography'), $this->environment->load->libraries);
        $this->assertSame($expected === $title ? array() : array($title), $this->environment->typography->calls);
    }

    /**
     * Return normal and invalid template strings passed to Active Record.
     *
     * @return array
     */
    public static function filters()
    {
        $mapping = array(
            'entry' => array('entry_id' => 't.entry_id', 'url_title' => 't.url_title', 'channel_id' => 't.channel_id', 'channel' => 'ch.channel_name'),
            'category' => array('category_id' => 'c.cat_id', 'url_title' => 'c.cat_url_title', 'category_group' => 'c.group_id'),
            'channel' => array('channel_id' => 'channel_id', 'channel_name' => 'channel_name'),
            'site' => array('site_id' => 'site_id', 'site_name' => 'site_name'),
        );
        $vectors = array();
        foreach ($mapping as $tag => $filters) {
            foreach ($filters as $parameter => $column) {
                foreach (array(null, false, '', '0', '1', "x' OR 1=1 --") as $value) {
                    $vectors[] = array($tag, $parameter, $column, $value);
                }
            }
        }

        return $vectors;
    }

    /**
     * Preserve filter values as bound Active Record arguments.
     *
     * @dataProvider filters
     * @param string $tag
     * @param string $parameter
     * @param string $column
     * @param mixed $value
     * @return void
     */
    public function testFiltersUseBoundValues($tag, $parameter, $column, $value)
    {
        $this->environment->TMPL->tagparams = array($parameter => $value);
        $this->environment->db->results = array(array());
        $this->plugin()->$tag();
        $where = array_values(array_filter($this->environment->db->calls, function ($call) { return $call[0] === 'where'; }));

        $this->assertSame($value ? array(array('where', $column, $value)) : array(), $where);
    }

    /**
     * Strip legacy category prefixes without changing the rest of the identifier.
     *
     * @return void
     */
    public function testCategoryPrefixAndCombinedFilters()
    {
        $this->environment->TMPL->tagparams = array('category_id' => 'C12', 'url_title' => 'news', 'category_group' => '3');
        $this->environment->db->results = array(array());
        $this->plugin()->category();

        $this->assertSame(array(
            array('select', 'cat_name AS title', false), array('from', 'exp_categories AS c'),
            array('where', 'c.cat_id', '12'), array('where', 'c.cat_url_title', 'news'),
            array('where', 'c.group_id', '3'), array('limit', 1), array('get'),
        ), $this->environment->db->calls);
    }

    /**
     * Return custom field selection and fallback branches.
     *
     * @return array
     */
    public static function customFields()
    {
        return array(
            array('entry', 'channel_fields', false, 'd.field_id_7 AS title'),
            array('entry', 'channel_fields', 'yes', "IF(d.field_id_7='',t.title,d.field_id_7) AS title"),
            array('category', 'category_fields', false, 'd.field_id_7 AS title'),
            array('category', 'category_fields', 'yes', "IF(d.field_id_7='',c.cat_name,d.field_id_7) AS title"),
        );
    }

    /**
     * Resolve custom fields once per request and join their storage table.
     *
     * @dataProvider customFields
     * @param string $tag
     * @param string $table
     * @param string|false $fallback
     * @param string $select
     * @return void
     */
    public function testCustomFieldsAreSelectedAndCached($tag, $table, $fallback, $select)
    {
        $name = $tag . '_' . ($fallback ?: 'default');
        $this->environment->TMPL->tagparams = array('custom_field' => $name, 'fallback' => $fallback, 'format' => 'no');
        $this->environment->db->results = array(array(array('field_id' => 7)), array(array('title' => 'custom')), array(array('title' => 'cached')));
        $plugin = $this->plugin();

        $this->assertSame('custom', $plugin->$tag());
        $this->assertSame('cached', $plugin->$tag());
        $calls = $this->environment->db->calls;
        $this->assertSame(array(array('select', 'field_id'), array('from', $table), array('where', 'field_name', $name), array('get')), array_slice($calls, 0, 4));
        $this->assertSame(array('select', $select, false), $calls[4]);
        $this->assertContains(array('join', $tag === 'entry' ? 'exp_channel_data AS d' : 'exp_category_field_data AS d', $tag === 'entry' ? 'd.entry_id = t.entry_id' : 'd.cat_id = c.cat_id'), $calls);
        $this->assertCount(1, array_filter($calls, function ($call) use ($table) { return $call === array('from', $table); }));
        $this->assertSame(array(), $this->environment->db->results);
    }

    /**
     * Return missing-field outcomes for both tag types.
     *
     * @return array
     */
    public static function missingFields()
    {
        return array(array('entry', 'yes'), array('entry', false), array('category', 'yes'), array('category', false));
    }

    /**
     * Return a visible error or continue with the built-in title for missing fields.
     *
     * @dataProvider missingFields
     * @param string $tag
     * @param string|false $showError
     * @return void
     */
    public function testMissingCustomField($tag, $showError)
    {
        $name = 'missing_' . $tag . '_' . ($showError ?: 'silent');
        $this->environment->TMPL->tagparams = array('custom_field' => $name, 'show_error' => $showError);
        $this->environment->db->results = array(array(array('field_id' => null)));
        if (!$showError) {
            $this->environment->db->results[] = array();
        }

        $result = $this->plugin()->$tag();
        $this->assertSame($showError ? "Custom field '$name' not found" : '', $result);
        $this->assertCount($showError ? 4 : ($tag === 'entry' ? 9 : 8), $this->environment->db->calls);
    }

    /**
     * Return pages-URI mappings, missing sites, and unmatched paths.
     *
     * @return array
     */
    public static function pageSettings()
    {
        return array(
            'mapped' => array(array(1 => array('uris' => array(21 => '/about/'))), '/about/', false, 21),
            'unmapped-silent' => array(array(1 => array('uris' => array(21 => '/other/'))), '/about/', false, null),
            'unmapped-error' => array(array(1 => array('uris' => array())), '/about/', 'yes', null),
            'missing-site' => array(array(), '/about/', 'yes', null),
            'exact-slashes' => array(array(1 => array('uris' => array(21 => '/about/'))), 'about', 'yes', null),
        );
    }

    /**
     * Limit pages queries only when the URI exists on the current site.
     *
     * @dataProvider pageSettings
     * @param array $pages
     * @param string $uri
     * @param string|false $showError
     * @param int|null $entryId
     * @return void
     */
    public function testPageUriFilters($pages, $uri, $showError, $entryId)
    {
        $this->environment->config->config['site_pages'] = $pages;
        $this->environment->TMPL->tagparams = array('pages_uri' => $uri, 'show_error' => $showError);
        $this->environment->db->results = array(array());

        $this->assertSame($showError && $entryId === null ? "Pages URI '$uri' not found" : '', $this->plugin()->entry());
        $where = array_values(array_filter($this->environment->db->calls, function ($call) { return $call[0] === 'where'; }));
        $this->assertSame($entryId === null ? array() : array(array('where', 't.entry_id', $entryId)), $where);
    }
}
