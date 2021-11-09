<?php

namespace Codewiser\Polyglot\Contracts;

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
     * Get lost of supported locales.
     *
     * @return array
     */
    public function getLocales(): array;
}