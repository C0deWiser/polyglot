<?php

namespace Codewiser\Polyglot\Collections;

use Codewiser\Polyglot\Contracts\EntryCollectionContract;
use Codewiser\Polyglot\Contracts\StatisticsContract;
use Codewiser\Polyglot\Statistics;
use Illuminate\Support\Collection;
use Sepia\PoParser\Catalog\Entry;

/**
 * Collection of Laravel Translator strings.
 */
class StringCollection extends Collection implements EntryCollectionContract
{
    /**
     * @param Entry $entry
     * @return Entry|null
     */
    public function exists(Entry $entry): ?Entry
    {
        return $this->first(function (Entry $item) use ($entry) {
            return $entry->getMsgId() == $item->getMsgId();
        });
    }

    public function untranslated(): EntryCollectionContract
    {
        return $this
            ->reject(function (Entry $entry) {
                return $entry->getMsgStr();
            });
    }

    public function translated(): EntryCollectionContract
    {
        return $this
            ->filter(function (Entry $entry) {
                return $entry->getMsgStr();
            });
    }

    public function fuzzy(): EntryCollectionContract
    {
        // Strings have no fuzzy flag
        return $this->reject();
    }

    public function obsolete(): EntryCollectionContract
    {
        // Strings have no obsolete flag
        return $this->reject();
    }

    public function statistics(): StatisticsContract
    {
        return new Statistics($this);
    }

    public function api(): EntryCollectionContract
    {
        return $this
            ->sort(function (Entry $left, Entry $right) {
                // Untranslated
                // Translated
                // Alphabet

                if ($left->getMsgStr() && !$right->getMsgStr()) {
                    // Left is down, right is up
                    return 1;
                }
                if (!$left->getMsgStr() && $right->getMsgStr()) {
                    // Left is up, right is down
                    return -1;
                }

                return strcasecmp($left->getMsgId(), $right->getMsgId());
            })
            ->map(function (Entry $entry) {
                return [
                    'msgid' => $entry->getMsgId(),
                    'msgstr' => $entry->getMsgStr()
                ];
            })
            ->values();
    }
}