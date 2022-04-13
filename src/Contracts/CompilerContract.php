<?php

namespace Codewiser\Polyglot\Contracts;

use Codewiser\Polyglot\FileSystem\Contracts\FileContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;

interface CompilerContract
{
    /**
     * Set source filename.
     *
     * @param FileHandlerContract|string $source
     */
    public function setSource($source): void;

    /**
     * Set target filename.
     *
     * @param FileContract|string $target
     */
    public function setTarget($target): void;

    /**
     * Compile target file.
     */
    public function compile();
}
