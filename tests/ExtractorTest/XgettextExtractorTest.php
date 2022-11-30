<?php

namespace Tests\ExtractorTest;

use Codewiser\Polyglot\Contracts\ExtractorContract;
use Codewiser\Polyglot\Xgettext\Precompiler;
use Codewiser\Polyglot\Xgettext\XgettextExtractor;
use Illuminate\Filesystem\Filesystem;

class XgettextExtractorTest extends ExtractorTest
{

    protected function getExtractor(): ExtractorContract
    {
        $extractor = new XgettextExtractor('Unit test');

        $extractor->setFilesystem(new Filesystem());
        $extractor->setTempPath($this->temp_path);
        $extractor->setBasePath($this->base_path);

        $precompiler = new Precompiler();
        $precompiler->setFilesystem(new Filesystem);
        $precompiler->setBasePath($this->base_path);
        $precompiler->setTempPath($this->temp_path);
        $extractor->setPrecompiler($precompiler);

        return $extractor;
    }
}
