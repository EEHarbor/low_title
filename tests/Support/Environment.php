<?php

namespace LowTitleTests;

use ErrorException;
use RuntimeException;

class Template
{
    public $tagparams = array();
    public $log = array();

    /**
     * Fetch a template parameter with EE's missing/null default contract.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function fetch_param($name, $default = false)
    {
        if (!isset($this->tagparams[$name])) {
            return $default;
        }
        if ($name === 'url_title') {
            return $this->tagparams[$name];
        }
        switch ($this->tagparams[$name]) {
            case 'y':
            case 'on':
                return 'yes';
            case 'n':
            case 'off':
                return 'no';
            default:
                return $this->tagparams[$name];
        }
    }

    /**
     * Record a template log entry.
     *
     * @param string $message
     * @return void
     */
    public function log_item($message)
    {
        $this->log[] = $message;
    }
}

class QueryResult
{
    public $num_rows;
    private $rows;
    private $currentRow = 0;
    private $columnRow;

    /**
     * Store database rows using EE's row object shape.
     *
     * @param array $rows
     * @return void
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
        $this->num_rows = count($rows);
    }

    /**
     * Return the result count exposed by EE_DB_result.
     *
     * @return int
     */
    public function num_rows()
    {
        return $this->num_rows;
    }

    /**
     * Return a column, row object, or EE's empty result array.
     *
     * @param int|string $column
     * @return mixed
     */
    public function row($column = 0, $type = 'object')
    {
        if (!is_numeric($column)) {
            if (!is_array($this->columnRow)) {
                $this->columnRow = $this->row_array(0);
            }
            if (array_key_exists($column, $this->columnRow)) {
                return $this->columnRow[$column];
            }
            $column = 0;
        }

        return $type == 'object' ? $this->row_object($column) : $this->row_array($column);
    }

    /**
     * Return the selected result row using EE's persistent row-index contract.
     *
     * @param int|string $index
     * @return object|array
     */
    public function row_object($index = 0)
    {
        if (!$this->rows) {
            return array();
        }
        if ($index != $this->currentRow && isset($this->rows[$index])) {
            $this->currentRow = $index;
        }

        return (object) $this->rows[$this->currentRow];
    }

    /**
     * Return the selected row as an array, retaining EE's invalid-index fallback.
     *
     * @param int|string $index
     * @return array
     */
    public function row_array($index = 0)
    {
        return (array) $this->row_object($index);
    }

    /**
     * Return all result objects.
     *
     * @return array
     */
    public function result()
    {
        return array_map(function ($row) { return (object) $row; }, $this->rows);
    }
}

class Database
{
    public $calls = array();
    public $results = array();
    public $data_cache = array();
    public $initialDataCache = array();
    public $tableCheckCaches = array();
    public $tableExists = false;
    public $count = 0;
    public $fieldExists = true;
    public $journal;

    /**
     * Share the query journal across cloned EE database builders.
     *
     * @return void
     */
    public function __construct()
    {
        $this->journal = (object) array('calls' => array());
    }

    /**
     * Return a fresh builder with connection defaults over the same backend fixture.
     *
     * @return Database
     */
    public function newQuery()
    {
        $query = clone $this;
        $query->calls = array();
        $query->data_cache = $this->initialDataCache;
        $query->tableCheckCaches = array();
        $query->results =& $this->results;

        return $query;
    }

    /**
     * Record Active Record calls while rejecting unintended collaborators.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws RuntimeException
     */
    public function __call($method, $arguments)
    {
        $this->calls[] = array_merge(array($method), $arguments);
        $this->journal->calls[] = array_merge(array($method), $arguments);
        if (in_array($method, array('get', 'get_where', 'query'), true)) {
            if (!$this->results) {
                throw new RuntimeException('No result queued for ' . $method);
            }

            return new QueryResult(array_shift($this->results));
        }

        if ($method === 'field_exists') {
            return $this->fieldExists;
        }

        if ($method === 'table_exists') {
            $this->tableCheckCaches[] = $this->data_cache;

            return $this->tableExists;
        }

        if ($method === 'count_all_results') {
            return $this->count;
        }

        if (in_array($method, array('select', 'from', 'join', 'where', 'limit', 'insert', 'update', 'delete'), true)) {
            return $this;
        }

        throw new RuntimeException('Unexpected database call ' . $method);
    }
}

class Config
{
    public $config = array('site_id' => 1, 'site_pages' => array(), 'app_version' => '7.5.0');

    /**
     * Return a configured EE value.
     *
     * @param string $key
     * @return mixed
     */
    public function item($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : false;
    }
    /**
     * Return a configured path with one trailing slash.
     *
     * @param string $key
     * @return string
     */
    public function slash_item($key)
    {
        return rtrim($this->item($key), '/') . '/';
    }

}

class Loader
{
    public $libraries = array();
    public $helpers = array();
    public $views = array();

    /**
     * Record loaded EE libraries.
     *
     * @param string $name
     * @return void
     */
    public function library($name)
    {
        $this->libraries[] = $name;
    }

    /**
     * Load the schema builder already configured for this request.
     *
     * @return void
     */
    public function dbforge()
    {
    }

    /**
     * Record loaded EE helpers.
     *
     * @param string $name
     * @return void
     */
    public function helper($name)
    {
        $this->helpers[] = $name;
    }
    /**
     * Render a legacy EE view through an observable seam.
     *
     * @param string $view
     * @param array $variables
     * @param bool $return
     * @return string
     */
    public function view($view, $variables, $return)
    {
        $this->views[] = array($view, $variables, $return);

        return 'rendered:' . $view;
    }

}

class Typography
{
    public $calls = array();

    /**
     * Provide an observable formatting seam for query behavior tests.
     *
     * @param string $title
     * @return string
     */
    public function format_characters($title)
    {
        $this->calls[] = $title;

        return 'formatted:' . $title;
    }
}

class Environment extends \stdClass
{
    public $TMPL;
    public $db;
    public $config;
    public $load;
    public $typography;
    public $logger;
    public $services = array();

    /**
     * Create independent collaborators for one test.
     *
     * @return void
     */
    public function __construct()
    {
        $this->TMPL = new Template;
        $this->db = new Database;
        $database = $this->db;
        $this->services['db'] = function () use ($database) { return $database->newQuery(); };
        $this->config = new Config;
        $this->load = new Loader;
        $this->typography = new Typography;
        $this->logger = new class {
            public $messages = array();

            /**
             * Record developer cache diagnostics.
             *
             * @param string $message
             * @return void
             */
            public function developer($message)
            {
                $this->messages[] = $message;
            }
        };
    }

    /**
     * Register an unloaded service through the EE facade's public contract.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws RuntimeException
     */
    public function set($name, $value)
    {
        if ($this->has($name)) {
            throw new RuntimeException("Cannot overwrite {$name} on the loader.");
        }

        $this->$name = $value;
    }

    /**
     * Check loaded legacy services independently of DI service factories.
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, get_object_vars($this));
    }

    /**
     * Remove a service through the EE super object's public contract.
     *
     * @param string $name
     * @return void
     */
    public function remove($name)
    {
        unset($this->$name);
    }

    /**
     * Reject source/harness diagnostics, including @, while respecting PHPUnit's internal probes.
     *
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @return false
     * @throws ErrorException
     */
    public static function diagnostic($severity, $message, $file, $line)
    {
        $tooling = strpos($file, '/tests/.phpunit/phpunit-') !== false || (defined('__PHPUNIT_PHAR_ROOT__') && strpos($file, __PHPUNIT_PHAR_ROOT__ . '/') === 0);
        if ($tooling && !(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }
}
