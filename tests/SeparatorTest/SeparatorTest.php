<?php

namespace Tests\SeparatorTest;

use Codewiser\Polyglot\Contracts\ExtractorContract;
use Codewiser\Polyglot\Contracts\SeparatorContract;
use Codewiser\Polyglot\Xgettext\XgettextExtractor;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Tests\Environment;

abstract class SeparatorTest extends TestCase
{
    use Environment;

    /**
     * Setup separator instance to run tests.
     *
     * @return SeparatorContract
     */
    abstract protected function getSeparator(): SeparatorContract;

    /**
     * Setup extractor instance to get extracted strings.
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

    public function testSeparation()
    {
        $separator = $this->getSeparator();

        $extracted = $this->getExtractor()->extract();

        $separator->setSource($extracted);

        $this->assertNull($separator->getExtractedKeys());
        $this->assertNull($separator->getExtractedStrings());

        $separator->separate();

        $this->assertNotNull($separator->getExtractedKeys());
        $this->assertNotNull($separator->getExtractedStrings());

        $this->assertEquals(
            $extracted->allEntries()->count(),
            $separator->getExtractedKeys()->allEntries()->count() +
            $separator->getExtractedStrings()->allEntries()->count()
        );
    }
}