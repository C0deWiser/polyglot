<?php

namespace Tests;

use Codewiser\Polyglot\StringsCollector;

class StringsCollectorTest extends \PHPUnit\Framework\TestCase
{
    protected $pot = __DIR__ . '/resources/lang/test.pot';
    protected StringsCollector $collector;

    protected function setUp(): void
    {
        $this->collector = new StringsCollector(
            'unit-test',
            __DIR__,
            [__DIR__ . '/sources'],
            $this->pot
        );
        $this->collector->exclude([__DIR__ . '/resources/lang']);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

//        if (file_exists($this->pot))
//            unlink($this->pot);
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

    public function testCollectStrings()
    {
        $this->collector->collect();

        $strings = $this->collector->getStrings(
            $this->collector->getPortableObjectTemplate()
        );

        $this->assertGreaterThan(0, $strings->count());

        $this->assertTrue(file_exists($this->pot));
    }
}