<?php

namespace Tests;

use Codewiser\Translation\Collectors\StringsCollector;
use Codewiser\Translation\Contracts\CollectorInterface;

class StringsCollectorTest extends \PHPUnit\Framework\TestCase
{
    protected $pot = __DIR__ . '/test.pot';
    /**
     * @var StringsCollector
     */
    protected $collector;

    protected function setUp(): void
    {
        $this->collector = new StringsCollector(__DIR__, ['en', 'es'], __DIR__ . '/resources/lang');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (file_exists($this->pot))
            unlink($this->pot);
    }

    public function testListingPhpOnly()
    {
        $listing = $this->collector
            ->resourceListing(__DIR__ . '/sources/', '*.php')
            ->toArray();

        $this->assertTrue(in_array(__DIR__ . '/sources/php/first.php', $listing));
        $this->assertTrue(in_array(__DIR__ . '/sources/php/second.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/js/first.js', $listing));

    }

    public function testListingJsOnly()
    {
        $listing = $this->collector
            ->resourceListing(__DIR__ . '/sources/', '*.js')
            ->toArray();

        $this->assertFalse(in_array(__DIR__ . '/sources/php/first.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/php/second.php', $listing));
        $this->assertTrue(in_array(__DIR__ . '/sources/js/first.js', $listing));

    }

    public function testListingPhpExcluding()
    {
        $listing = $this->collector
            ->resourceListing(__DIR__ . '/sources/', '*.php', [__DIR__ . '/sources/php/first.php'])
            ->toArray();

        $this->assertFalse(in_array(__DIR__ . '/sources/php/first.php', $listing));
        $this->assertTrue(in_array(__DIR__ . '/sources/php/second.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/js/first.js', $listing));

    }

    public function testListingJsExcluding()
    {
        $listing = $this->collector
            ->resourceListing(__DIR__ . '/sources/', '*.js', [__DIR__ . '/sources/js'])
            ->toArray();

        $this->assertFalse(in_array(__DIR__ . '/sources/php/first.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/php/second.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/js/first.js', $listing));

    }

    public function testCollectStrings($unlink = true)
    {
        if (file_exists($this->pot)) {
            unlink($this->pot);
        }

        $this->collector->collectStrings(__DIR__ . '/sources', $this->pot);

        $this->assertTrue(file_exists($this->pot));
    }

    public function testMergeRecursive()
    {
        $scanner = $this->collector;
        $merged = $scanner->mergeArrayRecursive([], 'path.to.string', 'path.to.string');
        $merged = $scanner->mergeArrayRecursive($merged, 'path.to.second', 'path.to.second');
        $merged = $scanner->mergeArrayRecursive($merged, 'path.to.string', 'fuck off');

        $this->assertTrue(isset($merged['path']['to']['string']));
        $this->assertTrue(isset($merged['path']['to']['second']));
        $this->assertEquals('path.to.string', $merged['path']['to']['string']);
    }

    public function testSavingStringEntry()
    {
        $scanner = $this->collector;
        $scanner->mergeStringEntry(__DIR__ . '/resources/lang', 'en', 'My string');
        $this->assertTrue(file_exists(__DIR__ . '/resources/lang/en.json'));
        unlink(__DIR__ . '/resources/lang/en.json');
    }

    public function testSavingKeyEntry()
    {
        $scanner = $this->collector;
        $scanner->mergeKeyEntry(__DIR__ . '/resources/lang', 'en', 'auth.failed');
        $this->assertTrue(file_exists(__DIR__ . '/resources/lang/en/auth.php'));
        unlink(__DIR__ . '/resources/lang/en/auth.php');
    }

    public function testPopulate()
    {
        $this->testCollectStrings(false);
        $scanner = $this->collector;
        $scanner->populate($this->pot, __DIR__ . '/resources/lang', ['en', 'es']);
        unlink(__DIR__ . '/resources/lang/en.json');
        unlink(__DIR__ . '/resources/lang/es.json');
        unlink(__DIR__ . '/resources/lang/en/short.php');
        unlink(__DIR__ . '/resources/lang/es/short.php');
    }

    public function testCollect()
    {
        $scanner = $this->collector;
        $scanner->setIncludes([__DIR__ . '/sources/']);
        $strings = $scanner->parse()
            ->toArray();

        $this->assertTrue(in_array('One cat', $strings));
        $this->assertTrue(in_array('short.message', $strings));
    }
}