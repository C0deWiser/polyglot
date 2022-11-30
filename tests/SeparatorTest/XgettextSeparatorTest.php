<?php

namespace Tests\SeparatorTest;

use Codewiser\Polyglot\Contracts\ExtractorContract;
use Codewiser\Polyglot\Contracts\SeparatorContract;
use Codewiser\Polyglot\Xgettext\Precompiler;
use Codewiser\Polyglot\Xgettext\XgettextExtractor;
use Codewiser\Polyglot\Xgettext\XgettextSeparator;
use Illuminate\Filesystem\Filesystem;

class XgettextSeparatorTest extends SeparatorTest
{
    protected function getExtractor(): ExtractorContract
    {
        $extractor = new XgettextExtractor('Unit test');

        $extractor->setFilesystem(new Filesystem());
        $extractor->setTempPath($this->temp_path);
        $extractor->setBasePath($this->base_path);
        $extractor->setSources([$this->sources_path]);

        $precompiler = new Precompiler();
        $precompiler->setFilesystem(new Filesystem);
        $precompiler->setBasePath($this->base_path);
        $precompiler->setTempPath($this->temp_path);
        $extractor->setPrecompiler($precompiler);

        return $extractor;
    }

    protected function getSeparator(): SeparatorContract
    {
        $separator = new XgettextSeparator();

        $separator->setFilesystem(new Filesystem());
        $separator->setTempPath($this->temp_path);
        $separator->setBasePath($this->base_path);

        return $separator;
    }
}
