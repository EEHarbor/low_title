<?php

namespace LowTitleTests;

use Low\Title\FluxCapacitor\Conduit\StaticCache;

class StaticCacheTest extends TestCase
{
    /**
     * Start every cache test with independent request state.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        StaticCache::clear();
    }

    /**
     * Remove process-global cache state after every vector.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        StaticCache::clear();
        parent::tearDown();
    }

    /**
     * Preserve string and composite keys and remove only the chosen item.
     *
     * @return void
     */
    public function testKeysAndDeletion()
    {
        $this->assertFalse(StaticCache::get('missing'));
        $this->assertFalse(StaticCache::has('missing'));
        StaticCache::put(array('entry', 12), array('title' => 'Hello'));
        StaticCache::set('other', 'untouched');

        $this->assertSame(array('title' => 'Hello'), StaticCache::get(array('entry', 12)));
        $this->assertTrue(StaticCache::has(array('entry', 12)));
        $this->assertSame(array(sha1('entry:12') => array('title' => 'Hello'), sha1('other') => 'untouched'), StaticCache::all());
        StaticCache::delete(array('entry', 12));
        $this->assertFalse(StaticCache::has(array('entry', 12)));
        $this->assertSame('untouched', StaticCache::get('other'));
        $this->assertTrue(StaticCache::clear());
        $this->assertSame(array(), StaticCache::all());
    }

    /**
     * Honor already processed keys without hashing them a second time.
     *
     * @return void
     */
    public function testPreprocessedKeys()
    {
        StaticCache::put('raw-key', 'value', true);
        $this->assertSame(array('raw-key' => 'value'), StaticCache::all());
        $this->assertSame('value', StaticCache::get('raw-key', true));
        $this->assertTrue(StaticCache::has('raw-key', true));
        StaticCache::delete('raw-key', true);
        $this->assertFalse(StaticCache::has('raw-key', true));
    }

    /**
     * Return false-like values whose exact cache round trip matters.
     *
     * @return array
     */
    public static function falseLikeValues()
    {
        return array(array(false), array(0), array('0'), array(''), array(array()), array(null));
    }

    /**
     * Require cached false-like values to retain their original type.
     *
     * @dataProvider falseLikeValues
     * @param mixed $value
     * @return void
     */
    public function testFalseLikeValuesRoundTrip($value)
    {
        StaticCache::put('false-like', $value);
        $this->assertSame($value, StaticCache::get('false-like'));
    }
}
