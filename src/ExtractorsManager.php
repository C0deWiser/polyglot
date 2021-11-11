<?php

namespace Codewiser\Polyglot;

use Illuminate\Support\Collection;

class ExtractorsManager
{
    protected FileLoader $loader;
    protected array $extractors = [];

    public function __construct(FileLoader $loader)
    {
        $this->loader = $loader;
    }

    public function loader(): FileLoader
    {
        return $this->loader;
    }

    public function addExtractor(Extractor $extractor): ExtractorsManager
    {
        $extractor->setLoader($this->loader);

        $this->extractors[$extractor->getTextDomain() . '/' . $extractor->getCategory()] = $extractor;

        return $this;
    }

    public function getExtractor(string $text_domain, int $category): ?Extractor
    {
        return $this->extractors[$text_domain . '/' . $category] ?? null;
    }

    /**
     * @return Collection|Extractor[]
     */
    public function extractors(): Collection
    {
        return collect($this->extractors);
    }
}