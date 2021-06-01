<?php

namespace Tests;

use Codewiser\Translation\Collectors\StringsCollector;
use Codewiser\Translation\Contracts\CollectorInterface;

class StringsCollectorTest extends \PHPUnit\Framework\TestCase
{
    protected $pot = __DIR__ . '/test.pot';

    protected function tearDown(): void
    {
        parent::tearDown();

        if (file_exists($this->pot))
            unlink($this->pot);
    }

    public function testListingPhpOnly()
    {
        $scanner = new StringsCollector(__DIR__);
        $listing = $scanner
            ->resourceListing(__DIR__ . '/sources/', '*.php')
            ->toArray();

        $this->assertTrue(in_array(__DIR__ . '/sources/php/first.php', $listing));
        $this->assertTrue(in_array(__DIR__ . '/sources/php/second.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/js/first.js', $listing));

    }

    public function testListingJsOnly()
    {
        $scanner = new StringsCollector(__DIR__);
        $listing = $scanner
            ->resourceListing(__DIR__ . '/sources/', '*.js')
            ->toArray();

        $this->assertFalse(in_array(__DIR__ . '/sources/php/first.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/php/second.php', $listing));
        $this->assertTrue(in_array(__DIR__ . '/sources/js/first.js', $listing));

    }

    public function testListingPhpExcluding()
    {
        $scanner = new StringsCollector(__DIR__);
        $listing = $scanner
            ->resourceListing(__DIR__ . '/sources/', '*.php', [__DIR__ . '/sources/php/first.php'])
            ->toArray();

        $this->assertFalse(in_array(__DIR__ . '/sources/php/first.php', $listing));
        $this->assertTrue(in_array(__DIR__ . '/sources/php/second.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/js/first.js', $listing));

    }

    public function testListingJsExcluding()
    {
        $scanner = new StringsCollector(__DIR__);
        $listing = $scanner
            ->resourceListing(__DIR__ . '/sources/', '*.js', [__DIR__ . '/sources/js'])
            ->toArray();

        $this->assertFalse(in_array(__DIR__ . '/sources/php/first.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/php/second.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/js/first.js', $listing));

    }

    public function testCollectStrings($unlink = true)
    {
        $scanner = new StringsCollector(__DIR__);

        if (file_exists($this->pot)) {
            unlink($this->pot);
        }

        $scanner->collectStrings(__DIR__ . '/sources', $this->pot);

        $this->assertTrue(file_exists($this->pot));
    }

    public function testMergeRecursive()
    {
        $scanner = new StringsCollector(__DIR__);
        $merged = $scanner->mergeArrayRecursive([], 'path.to.string', 'path.to.string');
        $merged = $scanner->mergeArrayRecursive($merged, 'path.to.second', 'path.to.second');
        $merged = $scanner->mergeArrayRecursive($merged, 'path.to.string', 'fuck off');

        $this->assertTrue(isset($merged['path']['to']['string']));
        $this->assertTrue(isset($merged['path']['to']['second']));
        $this->assertEquals('path.to.string', $merged['path']['to']['string']);
    }

    public function testSavingStringEntry()
    {
        $scanner = new StringsCollector(__DIR__);
        $scanner->mergeStringEntry(__DIR__ . '/resources/lang', 'en', 'My string');
        $this->assertTrue(file_exists(__DIR__ . '/resources/lang/en.json'));
        unlink(__DIR__ . '/resources/lang/en.json');
    }

    public function testSavingKeyEntry()
    {
        $scanner = new StringsCollector(__DIR__);
        $scanner->mergeKeyEntry(__DIR__ . '/resources/lang', 'en', 'auth.failed');
        $this->assertTrue(file_exists(__DIR__ . '/resources/lang/en/auth.php'));
        unlink(__DIR__ . '/resources/lang/en/auth.php');
    }

    public function testPopulate()
    {
        $this->testCollectStrings(false);
        $scanner = new StringsCollector(__DIR__);
        $scanner->populate($this->pot, __DIR__ . '/resources/lang', ['en', 'es']);
        unlink(__DIR__ . '/resources/lang/en.json');
        unlink(__DIR__ . '/resources/lang/es.json');
        unlink(__DIR__ . '/resources/lang/en/short.php');
        unlink(__DIR__ . '/resources/lang/es/short.php');
    }

    public function testCollect()
    {
        $scanner = new StringsCollector(__DIR__);
        $scanner->setIncludes([__DIR__ . '/sources/']);
        $strings = $scanner->parse()
            ->toArray();

        $this->assertTrue(in_array('One cat', $strings));
        $this->assertTrue(in_array('short.message', $strings));
    }
}