<?php

namespace Tests\FileHandlerTest;

use Codewiser\Polyglot\Contracts\ExtractorContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\JsonFileHandler;
use Codewiser\Polyglot\FileSystem\PhpFileHandler;
use Codewiser\Polyglot\Xgettext\XgettextExtractor;
use Illuminate\Filesystem\Filesystem;
use Sepia\PoParser\Catalog\Entry;

class PoFileHandlerTest extends FileHandlerTest
{
    protected function getExtractor(): ExtractorContract
    {
        $extractor = new XgettextExtractor('Unit test');

        $extractor->setFilesystem(new Filesystem());
        $extractor->setTempPath($this->temp_path);
        $extractor->setBasePath($this->base_path);
        $extractor->setSources([$this->sources_path]);

        $extractor->extract();

        return $extractor;
    }

    protected function getFile(): FileHandlerContract
    {
        return $this->getExtractor()->getExtracted();
    }

    protected function getKey()
    {
        return [
            'msgid' => 'My test string'
        ];
    }

    protected function getValue()
    {
        return [
            'msgstr' => 'My test string',
        ];
    }

    protected function assertEntry($expected, Entry $fetched)
    {
        $this->assertEquals($expected['msgstr'], $fetched->getMsgStr());
    }

    public function testPlurals()
    {
        $value = [
            'msgid_plural' => 'My test strings',
            'msgstr' => [
                'My test string',
                'My test strings'
            ]
        ];

        $file = $this->getFile();
        $file->putEntry($this->getKey(), $value);
        $fetched = $file->getEntry($this->getKey());

        $this->assertTrue($fetched->isPlural());
        $this->assertEquals($value['msgid_plural'], $fetched->getMsgIdPlural());
        $this->assertCount(2, $fetched->getMsgStrPlurals());
    }

    public function testFuzzy()
    {
        $value = $this->getValue() + ['fuzzy' => 1];

        $file = $this->getFile();
        $file->putEntry($this->getKey(), $value);
        $fetched = $file->getEntry($this->getKey());

        $this->assertTrue($fetched->isFuzzy());
    }
}