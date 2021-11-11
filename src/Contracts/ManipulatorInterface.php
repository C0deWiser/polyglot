<?php

namespace Codewiser\Polyglot\Contracts;

use Codewiser\Polyglot\Collections\FilesCollection;
use Illuminate\Support\Collection;
use Sepia\PoParser\Catalog\Entry;

interface ManipulatorInterface
{
    /**
     * Populate entries from given .pot file to translation files through every known locale.
     * It will not modify translations, just add new entries.
     *
     * @param string $template
     */
    public function populate(string $template);

    /**
     * Get list of supported locales. It is initially configured in config/polyglot.php
     * @return array
     */
    public function getLocales(): array;

    /**
     * Set new list of locales to populate collected strings through.
     *
     * @param array $locales
     */
    public function setLocales(array $locales): void;

    /**
     * Get dir that keeps collected strings. It is resources/lang by default.
     *
     * @return string
     */
    public function getStorage(): string;

    /**
     * Set dir to keep collected strings.
     *
     * @param string $storage
     */
    public function setStorage(string $storage): void;

    /**
     * Get all strings (for given local).
     *
     * @param string|null $locale
     * @return StringsCollectionInterface
     */
    public function all(string $locale = null): StringsCollectionInterface;

    /**
     * Get manipulator's file listing.
     *
     * @param string|null $locale
     * @return FilesCollection
     */
    public function files(string $locale = null): FilesCollection;
}