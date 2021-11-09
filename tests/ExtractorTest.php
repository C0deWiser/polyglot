<?php

namespace Tests;

class ExtractorTest extends WithExtractor
{
    public function testListingPhpOnly()
    {
        $listing = $this->extractor
            ->resourceListing(__DIR__ . '/sources/', '*.php')
            ->toArray();

        $this->assertTrue(in_array(__DIR__ . '/sources/php/first.php', $listing));
        $this->assertTrue(in_array(__DIR__ . '/sources/php/second.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/js/first.js', $listing));

    }

    public function testListingJsOnly()
    {
        $listing = $this->extractor
            ->resourceListing(__DIR__ . '/sources/', '*.js')
            ->toArray();

        $this->assertFalse(in_array(__DIR__ . '/sources/php/first.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/php/second.php', $listing));
        $this->assertTrue(in_array(__DIR__ . '/sources/js/first.js', $listing));

    }

    public function testListingPhpExcluding()
    {
        $listing = $this->extractor
            ->resourceListing(__DIR__ . '/sources/', '*.php', [__DIR__ . '/sources/php/first.php'])
            ->toArray();

        $this->assertFalse(in_array(__DIR__ . '/sources/php/first.php', $listing));
        $this->assertTrue(in_array(__DIR__ . '/sources/php/second.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/js/first.js', $listing));

    }

    public function testListingJsExcluding()
    {
        $listing = $this->extractor
            ->resourceListing(__DIR__ . '/sources/', '*.js', [__DIR__ . '/sources/js'])
            ->toArray();

        $this->assertFalse(in_array(__DIR__ . '/sources/php/first.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/php/second.php', $listing));
        $this->assertFalse(in_array(__DIR__ . '/sources/js/first.js', $listing));

    }

    public function testExtracting()
    {
        $this->extractor->extract();

        $strings = $this->extractor->getStrings(
            $this->extractor->getPortableObjectTemplate()
        );

        $this->assertGreaterThan(0, $strings->count());

        $this->assertTrue(file_exists($this->extractor->getPortableObjectTemplate()));
    }
}