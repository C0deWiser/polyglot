<?php


namespace Codewiser\Translation\Contracts;


interface CollectorInterface
{
    /**
     * Scan sources for the strings.
     *
     * @return $this
     */
    public function parse();

    /**
     * Store parsed strings into given path.
     *
     * @return void
     */
    public function store();

    /**
     * Get parsed strings.
     *
     * @return array
     */
    public function toArray();
}