<?php

namespace Codewiser\Polyglot\Collections;

use Codewiser\Polyglot\Contracts\StringsCollectionInterface;
use Codewiser\Polyglot\Contracts\StringsStatisticsContract;
use Illuminate\Support\Str;
use Sepia\PoParser\Catalog\Entry;
use function collect;

class EntryCollection extends \Illuminate\Support\Collection implements StringsCollectionInterface
{
    public function jsonStrings(): EntryCollection
    {
        return $this->filter(function (Entry $entry) {
            return !$this->hasDotSeparatedKey($entry->getMsgId());
        });
    }

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
     */
    public function hasDotSeparatedKey(string $msgid): ?array
    {
        if (preg_match('~^\S*$~', $msgid)
            && (Str::lower($msgid) === $msgid)
            && ($key = explode('.', $msgid))
            && (count($key) > 1)) {
            return $key;
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

    public function translated(): StringsCollectionInterface
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

    public function statistics(): StringsStatisticsContract
    {
        return new StringsStatistics($this);
    }

    public function api(): StringsCollectionInterface
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
            });
    }
}