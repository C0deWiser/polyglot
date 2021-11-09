<?php

namespace Tests;

use Codewiser\Polyglot\Extractor;
use Codewiser\Polyglot\ExtractorsManager;
use Codewiser\Polyglot\FileLoader;
use Illuminate\Filesystem\Filesystem;

class WithExtractor extends \PHPUnit\Framework\TestCase
{
    protected Extractor $extractor;
    protected FileLoader $loader;

    protected function setUp(): void
    {
        $extractor = new Extractor(
            'unit-test',
            [__DIR__ . '/sources']
        );

        $this->loader = new FileLoader(
            new Filesystem(),
            __DIR__ . '/resources',
            __DIR__,
            __DIR__.'/tmp'
        );

        $this->extractor = $extractor->setLoader($this->loader);

        parent::setUp();
    }
}