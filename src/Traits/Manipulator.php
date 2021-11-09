<?php

namespace Codewiser\Polyglot\Traits;

use Codewiser\Polyglot\FileLoader;

trait Manipulator
{
    protected array $locales;

    protected FileLoader $loader;

    protected \Illuminate\Filesystem\Filesystem $fs;

    protected string $storage;

    public function __construct(array $locales, FileLoader $loader)
    {
        $this->locales = $locales;
        $this->loader = $loader;
        $this->fs = $loader->filesystem();
        $this->storage = $loader->storage();
    }

    public function getLocales(): array
    {
        return $this->locales;
    }
}