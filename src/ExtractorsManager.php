<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Collections\FileCollection;
use Codewiser\Polyglot\Contracts\ExtractorContract;
use Codewiser\Polyglot\Contracts\ProducerContract;
use Codewiser\Polyglot\Contracts\SeparatorContract;
use Codewiser\Polyglot\Xgettext\XgettextExtractor;
use Codewiser\Polyglot\Traits\FilesystemSetup;
use Illuminate\Support\Collection;

class ExtractorsManager
{
    protected array $extractors = [];
    protected SeparatorContract $separator;
    protected ProducerContract $producersOfStrings;
    protected ProducerContract $producersOfKeys;

    /**
     * Replace previously configured extractors.
     *
     * @param array $extractors
     */
    public function setExtractors(array $extractors)
    {
        $this->extractors = [];

        foreach ($extractors as $extractor) {
            $this->addExtractor($extractor);
        }
    }

    public function addExtractor(ExtractorContract $extractor)
    {
        $this->extractors[$extractor->getTextDomain() . '/' . $extractor->getCategory()] = $extractor;
    }

    public function getExtractor(string $text_domain, int $category): ?ExtractorContract
    {
        return $this->extractors[$text_domain . '/' . $category] ?? null;
    }

    /**
     * @return Collection|ExtractorContract[]
     */
    public function extractors(): Collection
    {
        return collect($this->extractors);
    }

    public function setSeparator(SeparatorContract $separator)
    {
        $this->separator = $separator;
    }

    public function setProducersOfKeys(ProducerContract $producersOfKeys): void
    {
        $this->producersOfKeys = $producersOfKeys;
    }

    public function setProducerOfStrings(ProducerContract $producer)
    {
        $this->producersOfStrings = $producer;
    }

    /**
     * @return ProducerContract
     */
    public function getProducersOfStrings(): ProducerContract
    {
        return $this->producersOfStrings;
    }

    /**
     * @return ProducerContract
     */
    public function getProducersOfKeys(): ProducerContract
    {
        return $this->producersOfKeys;
    }

    /**
     * @return SeparatorContract
     */
    public function getSeparator(): SeparatorContract
    {
        return $this->separator;
    }

    public function extract(): ExtractorsManager
    {
        foreach ($this->extractors() as $extractor) {
            $extractor->extract();
        }

        return $this;
    }

    public function separate(): ExtractorsManager
    {
        foreach ($this->extractors() as $extractor) {
            $extracted = $extractor->getExtracted();

            $separator = $this->getSeparator();
            $separator->setSource($extracted);

            $separator->separate();
        }

        return $this;
    }

    public function produce(): ExtractorsManager
    {
        foreach ($this->extractors() as $extractor) {
            $extracted = $extractor->getExtracted();

            $separator = $this->getSeparator();
            $separator->setSource($extracted);

            $producerOfKeys = $this->getProducersOfKeys();
            $producerOfKeys->setSource($separator->getExtractedKeys());
            $producerOfKeys->produce();

            $producerOfStrings = $this->getProducersOfStrings();
            $producerOfStrings->setSource($separator->getExtractedStrings());
            $producerOfStrings->produce();
        }
        return $this;
    }

    public function getExtracted(): FileCollection
    {
        $extractedFiles = new FileCollection();

        $this->extractors()
            ->each(function (ExtractorContract $extractor) use ($extractedFiles) {
                if ($extracted = $extractor->getExtracted()) {
                    $extractedFiles->add($extracted);
                }
            });

        return $extractedFiles;
    }
}