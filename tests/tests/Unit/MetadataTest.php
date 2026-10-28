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

}
