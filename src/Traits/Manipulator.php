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

    /**
     * Get list of supported locales. It is initially configured in config/polyglot.php
     * @return array
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * Set new list of locales to populate collected strings through.
     *
     * @param array $locales
     */
    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }

    /**
     * Get dir that keeps collected strings. It is resources/lang by default.
     *
     * @return string
     */
    public function getStorage(): string
    {
        return $this->storage;
    }

    /**
     * Set dir to keep collected strings.
     *
     * @param string $storage
     */
    public function setStorage(string $storage): void
    {
        $this->storage = $storage;
    }
}