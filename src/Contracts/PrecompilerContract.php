<?php

namespace Codewiser\Polyglot\Contracts;

use Codewiser\Polyglot\FileSystem\Contracts\FileContract;

interface PrecompilerContract
{
    /**
     * Precompile file to ready to parse temp copy (copies).
     *
     * @return array<FileContract>
     */
    public function compiled(string $filename): array;
}
