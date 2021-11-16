<?php

namespace Codewiser\Polyglot\Contracts;


use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;

/**
 * Search and extracts translation strings from source codes.
 */
interface ExtractorContract
{
    /**
     * Set list of source files and directories.
     *
     * @param array $sources
     */
    public function setSources(array $sources): void;

    /**
     * Get list of source files and directories.
     *
     * @return array
     */
    public function getSources(): array;

    /**
     * Exclude some files and folders from search.
     *
     * @param array $exclude
     */
    public function setExclude(array $exclude): void;

    /**
     * Get list of source code excludes.
     *
     * @return array
     */
    public function getExclude(): array;

    /**
     * Start extracting strings, returns file with the extracted strings.
     *
     * @return FileHandlerContract
     */
    public function extract(): FileHandlerContract;

    /**
     * Get file with the previously extracted strings.
     *
     * @return FileHandlerContract|null
     */
    public function getExtracted(): ?FileHandlerContract;
}