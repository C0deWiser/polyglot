<?php

namespace Codewiser\Polyglot\FileSystem\Contracts;

use Codewiser\Polyglot\Contracts\EntryCollectionContract;
use Codewiser\Polyglot\Contracts\StatisticsContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileContract;
use Sepia\PoParser\Catalog\Entry;

interface FileHandlerContract extends FileContract
{
    /**
     * Get all entries.
     *
     * @return EntryCollectionContract|Entry[]
     */
    public function allEntries(): EntryCollectionContract;

    /**
     * Search for an entry.
     *
     * @param mixed $key
     * @return Entry|null
     */
    public function getEntry($key): ?Entry;

    /**
     * Insert or update an entry.
     *
     * @param mixed $key
     * @param mixed $value
     * @return bool
     */
    public function putEntry($key, $value): bool;

    /**
     * Delete an entry.
     *
     * @param mixed $key
     * @return bool
     */
    public function removeEntry($key): bool;

    /**
     * Get file entries statistics.
     *
     * @return StatisticsContract
     */
    public function statistics(): StatisticsContract;
}