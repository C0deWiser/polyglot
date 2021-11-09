<?php

namespace Codewiser\Polyglot\Collections;

use Illuminate\Support\Str;
use Sepia\PoParser\Catalog\Entry;
use function collect;

class EntryCollection extends \Illuminate\Support\Collection
{
    public function stringKeyed(): EntryCollection
    {
        return $this->filter(function (Entry $entry) {
            return !$this->hasDotSeparatedKey($entry->getMsgId());
        });
    }

    public function dotKeyed(): EntryCollection
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
                    if (collect($entry->getMsgStrPlurals())->reject(function ($string) {
                        return $string;
                    })->isNotEmpty()) {
                        return true;
                    }
                } else {
                    if (!$entry->getMsgStr()) {
                        return true;
                    }
                }
                return false;
            });
    }
}