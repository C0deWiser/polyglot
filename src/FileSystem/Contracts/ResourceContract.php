<?php

namespace Codewiser\Polyglot\FileSystem\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface ResourceContract extends Arrayable
{
    /**
     * Get the appropriate handler for the filename.
     *
     * @param string $filename
     * @return ResourceContract
     */
    public static function hydrate(string $filename): ResourceContract;

    /**
     * Get the resource name.
     *
     * @return string
     */
    public function __toString();

    /**
     * Gat parent directory.
     *
     * @return DirectoryContract
     */
    public function parent(): DirectoryContract;

    /**
     * Get resource basename.
     *
     * @return string
     */
    public function basename(): string;

    /**
     * Get resource path with basename.
     *
     * @return string
     */
    public function filename(): string;

    /**
     * Delete this resource.
     *
     * @return bool
     */
    public function delete(): bool;

    /**
     * Check if this resource exists.
     *
     * @return bool
     */
    public function exists(): bool;

    /**
     * Timestamp the resource was updated last time.
     *
     * @return int|null
     */
    public function lastModified(): ?int;

    /**
     * Make paths relative.
     *
     * @param string $path
     */
    public function relatedTo(string $path);

    public function asDirectory(): ?DirectoryContract;

    public function asFile(): ?FileContract;
}