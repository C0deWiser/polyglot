<?php


namespace Codewiser\Polyglot\Contracts;


use Illuminate\Contracts\Support\Arrayable;

interface CollectorInterface extends Arrayable
{
    /**
     * Scan sources for the strings.
     *
     * @return $this
     */
    public function parse(): CollectorInterface;

    /**
     * Store parsed strings into given path.
     *
     * @param string|null $pot File with collected strings.
     * @return void
     */
    public function store(string $pot = null): void;
}