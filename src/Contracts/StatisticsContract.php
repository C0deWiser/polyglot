<?php

namespace Codewiser\Polyglot\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface StatisticsContract extends Arrayable
{
    /**
     * Add strings collection to statistics.
     *
     * @param EntryCollectionContract $strings
     * @return mixed
     */
    public function add(EntryCollectionContract $strings);

    /**
     * Get total strings count.
     *
     * @return int
     */
    public function total(): int;

    /**
     * Get translated strings count.
     *
     * @return int
     */
    public function translated(): int;

    /**
     * Get untranslated strings count.
     *
     * @return int
     */
    public function untranslated(): int;

    /**
     * Get fuzzy strings count.
     *
     * @return int
     */
    public function fuzzy(): int;

    /**
     * Get obsolete strings count.
     *
     * @return int
     */
    public function obsolete(): int;

    /**
     * Get translation progress (0...1).
     *
     * @return float
     */
    public function progress(): float;

    /**
     * Get proportion of progress with problems (fuzzy etc.) (0...1).
     *
     * @return float
     */
    public function problems(): float;
}