<?php

namespace LowTitleTests;

class HarnessContractTest extends TestCase
{
    /**
     * Preserve numeric row selection, MySQL nulls, and EE's current-row fallback.
     *
     * @return void
     */
    public function testResultRowsMatchEeContract()
    {
        $result = new QueryResult(array(array('field_id' => null, 'title' => 'first'), array('field_id' => 7, 'title' => 'second')));
        $this->assertSame('second', $result->row(1)->title);
        $this->assertSame('second', $result->row(99)->title);
        $this->assertSame(array('field_id' => 7, 'title' => 'second'), $result->row('1', 'array'));
        $this->assertNull($result->row('field_id'));
        $this->assertSame('first', $result->row('missing')->title);
        $this->assertSame(array('field_id' => null, 'title' => 'first'), $result->row_array());
        $this->assertSame(array('field_id' => null, 'title' => 'first'), $result->row(0, 'other'));
        $this->assertSame(array(), (new QueryResult(array()))->row('field_id'));
    }

    /**
     * Create independent query state over one fixture backend and journal.
     *
     * @return void
     */
    public function testDatabaseServiceCreatesFreshBuilders()
    {
        $database = $this->environment->db;
        $database->data_cache = array('table_names' => array('existing'));
        $database->where('pending', 'caller-value');
        $database->results = array(array(array('title' => 'first')), array(array('title' => 'second')));
        $first = ee('db');
        $second = ee('db');
        $this->assertNotSame($database, $first);
        $this->assertNotSame($first, $second);
        $this->assertSame(array(), $first->calls);
        $this->assertSame(array(), $first->data_cache);
        $this->assertSame('first', $first->get()->row()->title);
        $this->assertSame('second', $second->get()->row()->title);
        $this->assertSame(array(array('where', 'pending', 'caller-value')), $database->calls);
        $this->assertSame(array(), $database->results);
        $this->assertCount(3, $database->journal->calls);
    }

    /**
     * Reject loaded values, including null, and permit registration after removal.
     *
     * @return void
     */
    public function testLoadedLegacyServiceContract()
    {
        $this->environment->services['replaced'] = function () { return 'factory-value'; };
        $this->assertFalse($this->environment->has('replaced'));
        foreach (array(null, 'original') as $value) {
            $this->environment->set('replaced', $value);
            $this->assertTrue($this->environment->has('replaced'));
            try {
                $this->environment->set('replaced', 'replacement');
                $this->fail('The EE facade must reject an already loaded service');
            } catch (\RuntimeException $exception) {
                $this->assertSame('Cannot overwrite replaced on the loader.', $exception->getMessage());
            }
            $this->assertSame($value, $this->environment->replaced);
            $this->assertSame('factory-value', ee('replaced'));
            $this->environment->remove('replaced');
            $this->assertFalse($this->environment->has('replaced'));
        }
        $this->environment->set('replaced', 'registered-after-removal');
        $this->assertSame('registered-after-removal', $this->environment->replaced);
        foreach (array('db', 'logger') as $name) {
            $collaborator = $this->environment->$name;
            $this->assertTrue($this->environment->has($name));
            $this->environment->remove($name);
            $this->assertFalse($this->environment->has($name));
            $this->environment->set($name, $collaborator);
            $this->assertSame($collaborator, $this->environment->$name);
            $this->assertSame($collaborator, ee()->$name);
        }
    }

    /**
     * Prefer POST for combined input and honor EE's strict boolean XSS-clean flag.
     *
     * @return void
     */
    public function testInputLookupAndCleaningContract()
    {
        $input = new Input;
        $input->getValues = array('conflict' => 'get', 'nullable' => 'get-fallback', 'only-get' => 'value');
        $input->postValues = array('conflict' => 'post', 'nullable' => null, 'false' => false, 'empty' => '');
        $input->cleaner = function ($value) { return 'clean:' . $value; };
        $this->assertSame('post', $input->get_post('conflict'));
        $this->assertSame('get-fallback', $input->get_post('nullable'));
        $this->assertSame('value', $input->get_post('only-get'));
        $this->assertFalse($input->get_post('false'));
        $this->assertSame('', $input->get_post('empty'));
        $this->assertFalse($input->post('missing'));
        $this->assertSame('clean:get', $input->get('conflict', true));
        $this->assertSame('get', $input->get('conflict', 1));
        $this->assertSame(array('get'), $input->cleaned);
    }

    /**
     * Return EE's parameter normalization and url_title exception vectors.
     *
     * @return array
     */
    public static function parameters()
    {
        return array(array('format', 'y', 'yes'), array('format', 'on', 'yes'), array('format', 'n', 'no'), array('format', 'off', 'no'), array('url_title', 'on', 'on'), array('url_title', 'off', 'off'), array('entry_id', '0', '0'), array('entry_id', 0, PHP_VERSION_ID < 80000 ? 'yes' : 0), array('format', null, 'default'));
    }

    /**
     * Match template normalization before shipped tags consume their parameters.
     *
     * @dataProvider parameters
     * @param string $name
     * @param mixed $value
     * @param mixed $expected
     * @return void
     */
    public function testTemplateParameterContract($name, $value, $expected)
    {
        $template = new Template;
        $template->tagparams[$name] = $value;
        $this->assertSame($expected, $template->fetch_param($name, 'default'));
    }

    /**
     * Preserve stateful persistent storage semantics, including false-like values.
     *
     * @return void
     */
    public function testPersistentStoreContract()
    {
        $cache = new StatefulCache;
        $this->assertFalse($cache->get('/missing'));
        $this->assertTrue($cache->save('/value', 0));
        $this->assertSame(0, $cache->get('/value'));
        $this->assertTrue($cache->delete('/value'));
        $this->assertFalse($cache->get('/value'));
    }

    /**
     * Restore request data and loader state when a nested test context ends.
     *
     * @return void
     */
    public function testCallerStateIsRestored()
    {
        $environment = $GLOBALS['low_title_ee'];
        $get = $_GET;
        $path = get_include_path();
        $loaders = spl_autoload_functions();
        $probe = new class extends TestCase {
            /**
             * Enter an isolated request fixture.
             *
             * @return void
             */
            public function begin()
            {
                parent::setUp();
            }

            /**
             * Restore the surrounding caller fixture.
             *
             * @return void
             */
            public function finish()
            {
                parent::tearDown();
            }
        };
        $probe->begin();
        $_GET = array('changed' => 'value');
        set_include_path($path . PATH_SEPARATOR . '/changed');
        spl_autoload_register(function ($class) { return null; });
        $probe->finish();
        $this->assertSame($environment, $GLOBALS['low_title_ee']);
        $this->assertSame($get, $_GET);
        $this->assertSame($path, get_include_path());
        $this->assertSame($loaders, spl_autoload_functions());
    }
    /**
     * Refuse a modified PHPUnit archive before the runtime can execute it.
     *
     * @return void
     */
    public function testLauncherRejectsModifiedTool()
    {
        $root = sys_get_temp_dir() . '/low-title-tool-check-' . getmypid() . '-' . bin2hex(random_bytes(6));
        $directory = $root . '/tests/.phpunit';
        mkdir($directory, 0777, true);
        foreach (array('verify.sh', 'run-phpunit.sh', 'versions.txt') as $name) {
            copy(dirname(__DIR__, 2) . '/.phpunit/' . $name, $directory . '/' . $name);
        }
        file_put_contents($directory . '/phpunit-9.phar', 'tampered');
        $marker = $root . '/executed';
        $runtime = $root . '/runtime';
        file_put_contents($runtime, '#!/bin/sh' . "\n" . 'touch ' . escapeshellarg($marker) . "\n");
        chmod($runtime, 0755);
        chmod($directory . '/verify.sh', 0755);
        try {
            $process = proc_open(array('/bin/bash', $directory . '/run-phpunit.sh', '--version'), array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, $root, array_merge($_ENV, array('LOW_TITLE_PHP_BIN' => $runtime)));
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exit = proc_close($process);
            $this->assertSame(1, $exit, $stdout . $stderr);
            $this->assertFileDoesNotExist($marker);
        } finally {
            foreach (glob($directory . '/*') as $file) {
                unlink($file);
            }
            unlink($runtime);
            if (is_file($marker)) {
                unlink($marker);
            }
            rmdir($directory);
            rmdir($root . '/tests');
            rmdir($root);
        }
    }

}
