<?php

namespace Codewiser\Polyglot\Traits;

use Codewiser\Polyglot\Collections\FileCollection;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;

trait AsProducer
{
    protected string $storage;
    protected array $locales;
    protected FileHandlerContract $source;
    protected FileCollection $populated;

    public function setStorage(string $storage): void
    {
        $this->storage = $storage;

        $this->populated = new FileCollection;
    }

    public function setLocales(array $locales): void
    {
        $this->locales = $locales;

        $this->populated = new FileCollection;
    }

    public function setSource(FileHandlerContract $source): void
    {
        $this->source = $source;

        $this->populated = new FileCollection;
    }

    public function getPopulated(): FileCollection
    {
        return $this->populated;
    }
}