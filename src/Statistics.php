<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Contracts\EntryCollectionContract;
use Codewiser\Polyglot\Contracts\StatisticsContract;

/**
 * Summarize statistics from many Entry Collections (fetched from files).
 */
class Statistics implements StatisticsContract
{
    /**
     * @var array|EntryCollectionContract[]
     */
    protected array $strings;

    public function __construct(EntryCollectionContract $strings = null)
    {
        $this->strings = [];

        if ($strings) {
            $this->strings[] = $strings;
        }
    }

    public function add(EntryCollectionContract $strings)
    {
        $this->strings[] = $strings;
    }

    public function toArray()
    {
        return [
            'total' => $this->total(),
            'translated' => $this->translated(),
            'untranslated' => $this->untranslated(),
            'fuzzy' => $this->fuzzy(),
            'progress' => $this->progress(),
            'problems' => $this->problems(),
        ];
    }

    public function total(): int
    {
        return $this->translated() + $this->untranslated();
    }

    public function translated(): int
    {
        $count = 0;
        foreach ($this->strings as $strings) {
            $count += $strings->translated()->count();
        }
        return $count;
    }

    public function untranslated(): int
    {
        $count = 0;
        foreach ($this->strings as $strings) {
            $count += $strings->untranslated()->count();
        }
        return $count;
    }

    public function fuzzy(): int
    {
        $count = 0;
        foreach ($this->strings as $strings) {
            $count += $strings->fuzzy()->count();
        }
        return $count;
    }

    public function obsolete(): int
    {
        $count = 0;
        foreach ($this->strings as $strings) {
            $count += $strings->obsolete()->count();
        }
        return $count;
    }

    public function progress(): float
    {
        $fuzzy = $this->fuzzy();
        $translated = $this->translated();
        if ($fuzzy < $translated) {
            $translated -= $fuzzy;
        } else {
            $translated = 0;
        }

        return $this->total() ? $translated / $this->total() : 0;
    }

    public function problems(): float
    {
        return $this->total() ? $this->fuzzy() / $this->total() : 0;
    }
}