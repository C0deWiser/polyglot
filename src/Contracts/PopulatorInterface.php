<?php

namespace Codewiser\Polyglot\Contracts;

use Sepia\PoParser\Catalog\Entry;

interface PopulatorInterface
{
    /**
     * Populate entries from given .pot file to translation files through every known locale.
     * It will not modify translations, just add new entries.
     *
     * @param string $pot
     */
    public function populate(string $pot);

    /**
     * Get path where populator stores their files.
     *
     * @return string
     */
    public function getStorage(): string;

    /**
     * Get lost of supported locales.
     *
     * @return array
     */
    public function getLocales(): array;
}