<?php

namespace Codewiser\Polyglot\Collections;

use Codewiser\Polyglot\Contracts\EntryCollectionContract;
use Codewiser\Polyglot\Contracts\StatisticsContract;
use Codewiser\Polyglot\Polyglot;
use Codewiser\Polyglot\Statistics;
use Illuminate\Support\Str;
use Sepia\PoParser\Catalog\Entry;
use function collect;

/**
 * Collection of Gettext PO entries.
 */
class EntryCollection extends \Illuminate\Support\Collection implements EntryCollectionContract
{
    /**
     * @param Entry $entry
     * @return Entry|null
     */
    public function exists(Entry $entry): ?Entry
    {
        return $this->first(function (Entry $item) use ($entry) {
            return
                $entry->getMsgId() == $item->getMsgId() &&
                $entry->getMsgCtxt() == $item->getMsgCtxt();

        });
    }

    /**
     * @return EntryCollection
     * @deprecated
     */
    public function jsonStrings(): EntryCollection
    {
        return $this->filter(function (Entry $entry) {
            return !$this->hasDotSeparatedKey($entry->getMsgId());
        });
    }

    /**
     * @return EntryCollection
     * @deprecated
     */
    public function phpStrings(): EntryCollection
    {
        return $this->filter(function (Entry $entry) {
            return $this->hasDotSeparatedKey($entry->getMsgId());
        });
    }

    /**
     * Check if given entry has dot.separated.key.
     *
     * @param string $msgid
     * @return array|null
     * @deprecated
     */
    public function hasDotSeparatedKey(string $msgid): ?array
    {
        if (Polyglot::isDotSeparatedKey($msgid)) {
            $keys = explode('::', $msgid);

            if (count($keys) == 2) {
                $keys = $keys[1];
            }

            return explode('.', $keys);
        } else {
            return null;
        }
    }

    public function fuzzy(): EntryCollection
    {
        return $this
            ->filter(function (Entry $entry) {
                return $entry->isFuzzy();
            });
    }

    public function untranslated(): EntryCollection
    {
        return $this
            ->filter(function (Entry $entry) {
                if ($entry->isPlural()) {
                    // Has untranslated plurals
                    return collect($entry->getMsgStrPlurals())
                        ->reject(function ($string) {
                            return $string;
                        })
                        ->isNotEmpty();
                } else {
                    return !$entry->getMsgStr();
                }
            });
    }

    public function translated(): EntryCollectionContract
    {
        return $this
            ->filter(function (Entry $entry) {
                if ($entry->isPlural()) {
                    // Has no untranslated plurals
                    return collect($entry->getMsgStrPlurals())
                        ->reject(function ($string) {
                            return $string;
                        })
                        ->isEmpty();
                } else {
                    return (bool)$entry->getMsgStr();
                }
            });
    }

    public function statistics(): StatisticsContract
    {
        return new Statistics($this);
    }

    public function api(): EntryCollectionContract
    {
        return $this
            ->map(function (Entry $entry) {
                $row = [];
                $row['msgid'] = $entry->getMsgId();

                if ($entry->isPlural()) {
                    $row['msgid_plural'] = $entry->getMsgIdPlural();
                    $row['msgstr'] = $entry->getMsgStrPlurals();
                } else {
                    $row['msgstr'] = $entry->getMsgStr();
                }

                $row['context'] = $entry->getMsgCtxt();

                $row['flags'] = $entry->getFlags();
                $row['fuzzy'] = $entry->isFuzzy();
                $row['reference'] = $entry->getReference();
                $row['developer_comments'] = $entry->getDeveloperComments();
                $row['comment'] = implode('. ', $entry->getTranslatorComments());

                return $row;
            })
            ->values();
    }
}