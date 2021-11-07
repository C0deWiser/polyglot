<?php

namespace Codewiser\Polyglot;

use Sepia\PoParser\Catalog\Entry;

class EntryCollection extends \Illuminate\Support\Collection
{
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