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
     * Get not obsolete records.
     *
     * @return EntryCollection
     */
    public function active(): EntryCollection
    {
        return $this
            ->reject(function (Entry $entry) {
                return $entry->isObsolete();
            });
    }

    public function fuzzy(): EntryCollection
    {
        return $this
            ->active()
            ->filter(function (Entry $entry) {
                return $entry->isFuzzy();
            });
    }

    public function untranslated(): EntryCollection
    {
        return $this
            ->active()
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
            ->active()
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
            ->sort(function (Entry $left, Entry $right) {
                // Fuzzy
                // Untranslated
                // Translated
                // Obsolete
                // ---
                // Without context
                // With context alphabetically
                // By message id alphabetically

                $isTranslated = fn(Entry $entry) => $entry->isPlural() ?
                    collect($entry->getMsgStrPlurals())->reject()->isEmpty() :
                    $entry->getMsgStr();

                if ($left->isObsolete() && !$right->isObsolete()) return 1;
                if (!$left->isObsolete() && $right->isObsolete()) return -1;
                if (!$left->isFuzzy() && $right->isFuzzy()) return 1;
                if ($left->isFuzzy() && !$right->isFuzzy()) return -1;
                if ($isTranslated($left) && !$isTranslated($right)) return 1;
                if (!$isTranslated($left) && $isTranslated($right)) return -1;
                if ($left->getMsgCtxt() && !$right->getMsgCtxt()) return 1;
                if (!$left->getMsgCtxt() && $right->getMsgCtxt()) return -1;

                if ($left->getMsgCtxt() && $right->getMsgCtxt() && $left->getMsgCtxt() !== $right->getMsgCtxt()) {
                    return strcasecmp($left->getMsgCtxt(), $right->getMsgCtxt());
                }

                return strcasecmp($left->getMsgId(), $right->getMsgId());
            })
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
                $row['obsolete'] = $entry->isObsolete();
                $row['reference'] = $entry->getReference();
                $row['developer_comments'] = $entry->getDeveloperComments();
                $row['comment'] = implode('. ', $entry->getTranslatorComments());

                return $row;
            })
            ->values();
    }

    public function obsolete(): EntryCollectionContract
    {
        return $this
            ->filter(function (Entry $entry) {
                return $entry->isObsolete();
            });
    }
}
