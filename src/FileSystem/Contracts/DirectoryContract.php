<?php

namespace Codewiser\Polyglot\FileSystem\Contracts;

use Codewiser\Polyglot\Collections\FileCollection;

interface DirectoryContract extends ResourceContract
{
    /**
     * Create this directory.
     *
     * @return void
     */
    public function ensureDirectoryExists(): void;

    /**
     * Get an array of all files in the directory.
     *
     * @return FileCollection|ResourceContract[]
     */
    public function files(): FileCollection;

    /**
     * Get all files (recursive) in the directory.
     *
     * @return FileCollection|ResourceContract[]
     */
    public function allFiles(): FileCollection;

    /**
     * Find path names matching a given pattern.
     *
     * @param  string  $pattern
     * @param  int  $flags
     * @return FileCollection|ResourceContract[]
     */
    public function glob(string $pattern, int $flags = 0): FileCollection;
}