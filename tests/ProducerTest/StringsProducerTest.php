<?php

namespace Tests\ProducerTest;

use Codewiser\Polyglot\Contracts\ExtractorContract;
use Codewiser\Polyglot\Contracts\ProducerContract;
use Codewiser\Polyglot\Contracts\SeparatorContract;
use Codewiser\Polyglot\Producers\ProducerOfJson;
use Codewiser\Polyglot\Producers\ProducerOfPhp;
use Codewiser\Polyglot\Xgettext\Precompiler;
use Codewiser\Polyglot\Xgettext\XgettextExtractor;
use Codewiser\Polyglot\Xgettext\XgettextSeparator;
use Illuminate\Filesystem\Filesystem;

class StringsProducerTest extends ProducerTest
{
    protected function getExtractor(): ExtractorContract
    {
        $extractor = new XgettextExtractor('Unit test');

        $extractor->setFilesystem(new Filesystem());
        $extractor->setTempPath($this->temp_path);
        $extractor->setBasePath($this->base_path);
        $extractor->setSources([$this->sources_path]);

        // Context is not recognized by php processor
        $extractor->setExclude([$this->context_path]);

        $precompiler = new Precompiler();
        $precompiler->setFilesystem(new Filesystem);
        $precompiler->setBasePath($this->base_path);
        $precompiler->setTempPath($this->temp_path);
        $extractor->setPrecompiler($precompiler);

        $extractor->extract();

        return $extractor;
    }

    protected function getSeparator(): SeparatorContract
    {
        $separator = new XgettextSeparator();

        $separator->setFilesystem(new Filesystem());
        $separator->setTempPath($this->temp_path);
        $separator->setBasePath($this->base_path);

        $separator->setSource($this->getExtractor()->getExtracted());
        $separator->separate();

        return $separator;
    }

    public function getDotSeparatedProducer(): ProducerContract
    {
        $producer = new ProducerOfPhp();

        $producer->setFilesystem(new Filesystem());

        return $producer;
    }

    public function getNaturalStringsProducer(): ProducerContract
    {
        $producer = new ProducerOfJson();

        $producer->setFilesystem(new Filesystem());

        return $producer;
    }
}
