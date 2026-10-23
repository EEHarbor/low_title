<?php

namespace LowTitleTests;

class MetadataTest extends TestCase
{
    /**
     * Return setup metadata exactly as shipped in addon.json.
     *
     * @return void
     */
    public function testAddonSetupMetadata()
    {
        $root = dirname(__DIR__, 3);
        $metadata = json_decode(file_get_contents($root . '/low_title/addon.json'), true);
        $autoloaders = spl_autoload_functions();
        try {
            $setup = require $root . '/low_title/addon.setup.php';
            $this->assertSame(array(
                'name' => $metadata['name'], 'description' => $metadata['description'], 'version' => $metadata['version'],
                'namespace' => $metadata['namespace'], 'author' => 'EEHarbor', 'author_url' => 'http://eeharbor.com/low_title',
                'docs_url' => 'http://eeharbor.com/low_title/documentation', 'settings_exist' => false,
            ), $setup);
            $this->assertSame($autoloaders, spl_autoload_functions());
        } finally {
            foreach (array_diff_key(spl_autoload_functions(), $autoloaders) as $autoloader) {
                spl_autoload_unregister($autoloader);
            }
        }
    }

    /**
     * Verify every archive measurement copy against the shipped archive bytes.
     *
     * @return void
     */
    public function testSourceInventoryProvenance()
    {
        $root = dirname(__DIR__, 3);
        $inventory = json_decode(file_get_contents($root . '/tests/source-inventory.json'), true);
        $archive = $root . '/low_title/low_title.phar';
        $this->assertSame($inventory['archive_sha256'], hash_file('sha256', $archive));
        foreach ($inventory['files'] as $origin => $source) {
            $path = $root . '/' . $origin;
            if (strpos($origin, '.phar/') !== false) {
                $path = 'phar://' . $path;
            }
            $contents = $origin === 'low_title/low_title.phar#stub' ? (new \Phar($archive))->getStub() : file_get_contents($path);
            $this->assertSame($source['sha256'], hash('sha256', $contents), $origin);
        }
    }
}
