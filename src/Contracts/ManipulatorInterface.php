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
     * Get list of supported locales.
     *
     * @return array
     */
    public function getLocales(): array;

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