<?php

namespace Codewiser\Polyglot\Xgettext\Precompilers;

use Codewiser\Polyglot\Contracts\PrecompilerContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileContract;
use Codewiser\Polyglot\FileSystem\FileHandler;
use Codewiser\Polyglot\Traits\FilesystemSetup;
use Illuminate\Support\Str;

class FallbackPrecompiler implements PrecompilerContract
{
    use FilesystemSetup;

    public function compiled(string $filename): array
    {
        $filename = new FileHandler($filename);

        return [
            $this->makeTemporaryCopy($filename)
        ];
    }

    /**
     * Make temporary copy of php file.
     *
     * @param FileContract $filename
     * @return FileContract
     */
    protected function makeTemporaryCopy(FileContract $filename): FileContract
    {
        $tmp = $this->makeTemporary($filename);

        $filename->copyTo($tmp);

        return $tmp;
    }

    /**
     * Make handler to new temporary file.
     *
     * @param FileContract|string $filename
     */
    protected function makeTemporary($filename): FileContract
    {
        $tmp = $this->temporize($filename);
        $tmp->delete();
        $tmp->parent()->ensureDirectoryExists();

        return $tmp;
    }
}
