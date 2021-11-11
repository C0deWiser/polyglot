<?php

namespace Codewiser\Polyglot\Collections;

use Codewiser\Polyglot\Contracts\StringsCollectionInterface;
use Codewiser\Polyglot\Contracts\StringsStatisticsContract;

class StringsStatistics implements StringsStatisticsContract
{
    /**
     * @var array|StringsCollectionInterface[]
     */
    protected array $strings;

    public function __construct(StringsCollectionInterface $strings)
    {
        $this->strings[] = $strings;
    }

    public function add(StringsCollectionInterface $strings)
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
        $count = 0;
        foreach ($this->strings as $strings) {
            $count += $strings->count();
        }
        return $count;
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