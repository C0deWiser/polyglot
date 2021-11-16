<?php

namespace Tests\ProducerTest;

use Codewiser\Polyglot\Contracts\ExtractorContract;
use Codewiser\Polyglot\Contracts\ProducerContract;
use Codewiser\Polyglot\Contracts\SeparatorContract;
use Codewiser\Polyglot\Producers\ProducerOfJson;
use Codewiser\Polyglot\Producers\ProducerOfPhp;
use Codewiser\Polyglot\Producers\ProducerOfPo;
use Codewiser\Polyglot\Xgettext\XgettextExtractor;
use Codewiser\Polyglot\Xgettext\XgettextSeparator;
use Illuminate\Filesystem\Filesystem;

class GettextProducerTest extends ProducerTest
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
        $producer = new ProducerOfPo();

        $producer->setFilesystem(new Filesystem());

        return $producer;
    }
}