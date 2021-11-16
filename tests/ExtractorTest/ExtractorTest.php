<?php

namespace Tests\ExtractorTest;

use Codewiser\Polyglot\Contracts\ExtractorContract;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Tests\Environment;

abstract class ExtractorTest extends TestCase
{
    use Environment;
    /**
     * Setup extractor instance to run tests.
     *
     * @return ExtractorContract
     */
    abstract protected function getExtractor(): ExtractorContract;

    protected function setUp(): void
    {
        parent::setUp();

        $filesystem = new Filesystem();
        $filesystem->cleanDirectory($this->temp_path);
    }

    public function testExtract()
    {
        $extractor = $this->getExtractor();

        $extractor->setSources([$this->sources_path]);

        $this->assertNull($extractor->getExtracted());

        $extractor->extract();

        $this->assertNotNull($extractor->getExtracted());
        $this->assertTrue($extractor->getExtracted()->exists());

        $entries = $extractor->getExtracted()->allEntries();

        $this->assertGreaterThan(0, $entries->count());
    }
}