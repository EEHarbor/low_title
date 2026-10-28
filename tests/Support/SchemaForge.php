<?php

namespace LowTitleTests;

class SchemaBackend
{
    public $tables = array();
    public $fields = array();
    public $primaryKeys = array();
}

class SchemaForge
{
    public $calls = array();
    public $db;
    private $fields = array();
    private $primaryKeys = array();

    /**
     * Retain a fresh database builder over an explicitly configured live schema.
     *
     * @param Database $database
     * @return void
     * @throws \RuntimeException
     */
    public function __construct(Database $database)
    {
        if (!$database->schema instanceof SchemaBackend) {
            throw new \RuntimeException('SchemaForge requires an explicit schema backend.');
        }
        $this->db = $database;
    }

    /**
     * Accumulate the successful table creation's exact field definitions.
     *
     * @param array $fields
     * @return void
     */
    public function add_field(array $fields)
    {
        $this->calls[] = array('add_field', $fields);
        $this->fields = array_merge($this->fields, $fields);
    }

    /**
     * Record ordinary and primary index declarations in native argument order.
     *
     * @param string|array $key
     * @param bool $primary
     * @return void
     */
    public function add_key($key, $primary = false)
    {
        $this->calls[] = array('add_key', $key, $primary);
        if ($primary) {
            $this->primaryKeys = array_merge($this->primaryKeys, (array) $key);
        }
    }

    /**
     * Publish successful table metadata and update only this builder's table cache.
     *
     * @param string $table
     * @return bool
     */
    public function create_table($table)
    {
        $this->calls[] = array('create_table', $table);
        if (!isset($this->db->data_cache['table_names'])) {
            $this->db->data_cache['table_names'] = array_map(static function ($name) {
                return 'exp_' . $name;
            }, $this->db->schema->tables);
        }
        // The native forge appends when array_search returns index zero.
        if (!array_search('exp_' . $table, $this->db->data_cache['table_names'])) {
            $this->db->data_cache['table_names'][] = 'exp_' . $table;
        }
        $this->db->schema->tables[] = $table;
        $this->db->schema->fields[$table] = array_keys($this->fields);
        $keys = array();
        foreach ($this->primaryKeys as $key) {
            $keys[] = array('Column_name' => $key, 'Non_unique' => '0');
        }
        $this->db->schema->primaryKeys[$table] = $keys;
        $this->fields = array();
        $this->primaryKeys = array();

        return true;
    }

    /**
     * Publish successful columns and invalidate only this builder's field cache.
     *
     * @param string $table
     * @param array $fields
     * @return bool
     */
    public function add_column($table, array $fields)
    {
        $this->calls[] = array('add_column', $table, $fields);
        $existing = isset($this->db->schema->fields[$table]) ? $this->db->schema->fields[$table] : array();
        $this->db->schema->fields[$table] = array_values(array_unique(array_merge($existing, array_keys($fields))));
        unset($this->db->data_cache['field_names'][$table]);

        return true;
    }
}
