<?php

namespace LowTitleTests;

use Low\Title\FluxCapacitor\Conduit\PersistentCache;
use Low\Title\FluxCapacitor\Conduit\StaticCache;

class PersistentCacheTest extends TestCase
{
    private $directory;
    private $originalPaths;

    /**
     * Allocate an isolated writable cache without changing application paths.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir() . '/low-title-cache-' . getmypid() . '-' . bin2hex(random_bytes(6));
        mkdir($this->directory);
        $this->originalPaths = array();
        foreach (array('cache_path' => $this->directory . '/', 'module_cache_path' => $this->directory . '/low_title/') as $name => $value) {
            $property = new \ReflectionProperty(PersistentCache::class, $name);
            if (PHP_VERSION_ID < 80100) {
                $property->setAccessible(true);
            }
            $this->originalPaths[$name] = $property->getValue();
            $property->setValue(null, $value);
        }
        StaticCache::clear();
    }

    /**
     * Restore static paths and remove only this test's temporary directory.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        foreach ($this->originalPaths as $name => $value) {
            $property = new \ReflectionProperty(PersistentCache::class, $name);
            if (PHP_VERSION_ID < 80100) {
                $property->setAccessible(true);
            }
            $property->setValue(null, $value);
        }
        $this->removeDirectory($this->directory);
        StaticCache::clear();
        parent::tearDown();
    }

    /**
     * Store serialized values and recover them across an empty request cache.
     *
     * @return void
     */
    public function testDiskRoundTripAndDeletion()
    {
        $value = array('title' => 'Hello', 'nullable' => null, 'zero' => 0);
        $this->assertFalse(PersistentCache::get('entry:12'));
        $this->assertCount(1, $this->environment->logger->messages);
        $this->assertTrue(PersistentCache::set(array('entry', 12), $value));
        $this->assertTrue(PersistentCache::has(array('entry', 12)));
        $this->assertSame($value, PersistentCache::get('entry:12'));
        StaticCache::clear();
        $this->assertSame($value, PersistentCache::get('entry:12'));
        $this->assertTrue(PersistentCache::delete(array('entry', 12)));
        $this->assertFalse(PersistentCache::has(array('entry', 12)));
        $this->assertTrue(PersistentCache::delete('not-found'));
    }

    /**
     * Clear only persistent entries while preserving unrelated static cache data.
     *
     * @return void
     */
    public function testClearKeepsUnrelatedStaticCache()
    {
        PersistentCache::set('first', 'one');
        PersistentCache::set('second', 'two');
        mkdir($this->directory . '/low_title/unrelated-directory');
        file_put_contents($this->directory . '/low_title/unrelated-directory/retained', 'untouched');
        StaticCache::put('unrelated', 'retained');

        $this->assertTrue(PersistentCache::clear());
        $this->assertSame(array($this->directory . '/low_title/unrelated-directory'), glob($this->directory . '/low_title/*'));
        $this->assertSame('untouched', file_get_contents($this->directory . '/low_title/unrelated-directory/retained'));
        $this->assertSame('retained', StaticCache::get('unrelated'));
        $this->assertFalse(StaticCache::has(sha1('first'), true));
        $this->assertFalse(StaticCache::has(sha1('second'), true));
    }

    /**
     * Create and repeatedly clear an empty module cache without diagnostics.
     *
     * @return void
     */
    public function testEmptyCacheCanBeClearedRepeatedly()
    {
        $this->assertTrue(PersistentCache::clear());
        $this->assertTrue(PersistentCache::clear());
        $this->assertSame(array(), glob($this->directory . '/low_title/*'));
    }

    /**
     * Require repeated disk-cache reads to preserve the caller's value shape.
     *
     * @return void
     */
    public function testRequestCacheSurvivesMissingDiskFile()
    {
        $value = array('title' => 'Hello');
        PersistentCache::set('repeat', $value);
        unlink($this->directory . '/low_title/' . sha1('repeat'));

        $this->assertSame($value, PersistentCache::get('repeat'));
    }
    /**
     * Require documented composite keys to work for reads as well as writes.
     *
     * @return void
     */
    public function testCompositeKeysRoundTrip()
    {
        $this->assertTrue(PersistentCache::set(array('entry', 12), 'title'));
        $this->assertSame('title', PersistentCache::get(array('entry', 12)));
    }
    /**
     * Create absent application and module cache directories inside this test's root.
     *
     * @return void
     */
    public function testMissingCacheDirectoriesAreCreated()
    {
        foreach (array('cache_path' => $this->directory . '/new-cache', 'module_cache_path' => $this->directory . '/new-cache/low_title/') as $name => $value) {
            $property = new \ReflectionProperty(PersistentCache::class, $name);
            if (PHP_VERSION_ID < 80100) {
                $property->setAccessible(true);
            }
            $property->setValue(null, $value);
        }
        $this->assertTrue(PersistentCache::set('created', 'value'));
        $this->assertFileExists($this->directory . '/new-cache/low_title/' . sha1('created'));
    }

    /**
     * Return generic and module-specific unwritable cache paths.
     *
     * @return array
     */
    public static function unwritablePaths()
    {
        return array(array(false), array(true));
    }

    /**
     * Reject writes and reads when either cache directory is unwritable.
     *
     * @dataProvider unwritablePaths
     * @param bool $module
     * @return void
     */
    public function testUnwritablePathsAreLogged($module)
    {
        mkdir($this->directory . '/low_title');
        $path = $module ? $this->directory . '/low_title' : $this->directory;
        chmod($path, 0555);
        try {
            $this->assertFalse(PersistentCache::get('unwritable'));
            $this->assertFalse(PersistentCache::set('unwritable', 'value'));
            $this->assertFalse(PersistentCache::delete('unwritable'));
            $this->assertFalse(PersistentCache::clear());
            $this->assertCount(4, $this->environment->logger->messages);
        } finally {
            chmod($path, 0777);
        }
    }

    /**
     * Log a failed file-helper write without pretending that the value was cached.
     *
     * @return void
     */
    public function testWriteFailureIsReported()
    {
        mkdir($this->directory . '/low_title');
        mkdir($this->directory . '/low_title/' . sha1('directory'));
        $this->assertFalse(PersistentCache::set('directory', 'value'));
        $this->assertCount(1, $this->environment->logger->messages);
        $this->assertFalse(PersistentCache::has('directory'));
    }

    /**
     * Require independent keys to remain independent in the request cache.
     *
     * @return void
     */
    public function testHashedKeyDoesNotReadAnotherKeysValue()
    {
        PersistentCache::set('source', 'original');
        PersistentCache::set(sha1('source'), 'separate');
        $this->assertSame('separate', PersistentCache::get(sha1('source')));
    }

    /**
     * Require the documented processed-key flag to avoid hashing a key twice.
     *
     * @return void
     */
    public function testProcessedKeyFlag()
    {
        PersistentCache::set('source', 'value');
        $this->assertTrue(PersistentCache::has(sha1('source'), true));
    }

    /**
     * Require corrupt serialized cache bytes to return a miss without a warning.
     *
     * @return void
     */
    public function testCorruptCacheIsHandled()
    {
        mkdir($this->directory . '/low_title');
        file_put_contents($this->directory . '/low_title/' . sha1('corrupt'), 'not serialized');
        $this->assertFalse(PersistentCache::get('corrupt'));
    }

    /**
     * Remove only this test's temporary tree without following symlinks.
     *
     * @param string $directory
     * @return void
     */
    private function removeDirectory($directory)
    {
        foreach (new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS) as $file) {
            if ($file->isDir() && !$file->isLink()) {
                $this->removeDirectory($file->getPathname());
                continue;
            }
            unlink($file->getPathname());
        }
        rmdir($directory);
    }

    /**
     * Require an existing file in either cache-directory slot to fail cleanly.
     *
     * @dataProvider unwritablePaths
     * @param bool $module
     * @return void
     */
    public function testNonDirectoryCachePathIsHandled($module)
    {
        $path = $this->directory . '/not-a-directory';
        file_put_contents($path, 'file');
        $property = new \ReflectionProperty(PersistentCache::class, $module ? 'module_cache_path' : 'cache_path');
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }
        $property->setValue(null, $path);
        $this->assertFalse(PersistentCache::set('blocked', 'value'));
    }

}
