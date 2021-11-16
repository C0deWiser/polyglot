<?php

namespace Codewiser\Polyglot\Contracts;

use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;

/**
 * Separates extracted strings into two channels,
 * one with dot.separated.keys,
 * second with 'Natural Strings'
 */
interface SeparatorContract
{
    /**
     * Start separation.
     *
     * @return bool
     */
    public function separate(): bool;

    /**
     * Set the source file with extracted strings.
     *
     * @param string $source
     */
    public function setSource(string $source);

    /**
     * Get file with the extracted dot.separated.keys.
     *
     * @return FileHandlerContract|null
     */
    public function getExtractedKeys(): ?FileHandlerContract;

    /**
     * Get file with the extracted 'Natural Strings'.
     *
     * @return FileHandlerContract|null
     */
    public function getExtractedStrings(): ?FileHandlerContract;
}