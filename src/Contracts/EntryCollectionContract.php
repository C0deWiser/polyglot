<?php

namespace Codewiser\Polyglot\Contracts;

use Illuminate\Support\Collection;
use Sepia\PoParser\Catalog\Entry;

/**
 * @mixin Collection
 *
 * @method Entry first(callable $callback = null, $default = null)
 */
interface EntryCollectionContract
{
    /**
     * Find entry by key.
     *
     * @param Entry $key
     * @return Entry|null
     */
    public function exists(\Sepia\PoParser\Catalog\Entry $entry): ?Entry;

    /**
     * Prepare data for api response.
     *
     * @return EntryCollectionContract|array[]
     */
    public function api(): EntryCollectionContract;

    /**
     * Get collection statistics.
     *
     * @return StatisticsContract
     */
    public function statistics(): StatisticsContract;

    /**
     * Get strings with translation.
     *
     * @return EntryCollectionContract|Entry[]
     */
    public function translated(): EntryCollectionContract;

    /**
     * Get strings without translation.
     *
     * @return EntryCollectionContract|Entry[]
     */
    public function untranslated(): EntryCollectionContract;

    /**
     * Get fuzzy strings.
     *
     * @return EntryCollectionContract|Entry[]
     */
    public function fuzzy(): EntryCollectionContract;

    /**
     * Get obsolete strings.
     *
     * @return EntryCollectionContract|Entry[]
     */
    public function obsolete(): EntryCollectionContract;
}