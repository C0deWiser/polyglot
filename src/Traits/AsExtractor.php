<?php

namespace Codewiser\Polyglot\Traits;

trait AsExtractor
{
    /**
     * Files and folder to search translation strings.
     *
     * @var array
     */
    protected array $sources = [];

    /**
     * Exclude files and folder from search.
     *
     * @var array
     */
    protected array $exclude = [];

    public function getSources(): array
    {
        return $this->sources;
    }

    public function getExclude(): array
    {
        return $this->exclude;
    }

    public function setSources(array $sources): void
    {
        $this->sources = $sources;
    }

    public function setExclude(array $exclude): void
    {
        $this->exclude = $exclude;
    }
}