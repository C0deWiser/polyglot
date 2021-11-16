<?php

namespace Codewiser\Polyglot\FileSystem\Contracts;

use Codewiser\Polyglot\Collections\FileCollection;
use Codewiser\Polyglot\FileSystem\Contracts\ResourceContract;

/**
 * File explorer (finder) interface.
 */
interface FinderContract
{
    /**
     * Bind finder to the root dir.
     *
     * @param string $base_dir
     */
    public function setRoot(string $base_dir);

    /**
     * Check if current path is root.
     *
     * @return bool
     */
    public function isRoot():bool;

    /**
     * Set current path (relative to root).
     *
     * @param string $path
     */
    public function setPath(string $path);

    /**
     * Get current path.
     *
     * @return ResourceContract
     */
    public function getPath(): ResourceContract;

    /**
     * Get parent dir.
     *
     * @return DirectoryContract|null
     */
    public function parent(): ?DirectoryContract;

    /**
     * Get an array of all files in a directory.
     *
     * @return FileCollection|ResourceContract[]
     */
    public function files(): FileCollection;

    /**
     * Get all files (recursive).
     *
     * @return FileCollection
     */
    public function allFiles(): FileCollection;
}