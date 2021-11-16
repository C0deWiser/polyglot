<?php

namespace Codewiser\Polyglot\FileSystem;

use Codewiser\Polyglot\Collections\FileCollection;
use Codewiser\Polyglot\FileSystem\Contracts\DirectoryContract;
use Codewiser\Polyglot\FileSystem\Contracts\ResourceContract;
use Symfony\Component\Finder\SplFileInfo;

class DirectoryHandler extends ResourceHandler implements DirectoryContract
{
    public function delete(): bool
    {
        return $this->filesystem->deleteDirectory($this);
    }

    public function ensureDirectoryExists(): void
    {
        $this->filesystem->ensureDirectoryExists($this);
    }

    public function files(): FileCollection
    {
        return $this->collect(
            array_merge(
                $this->filesystem->directories($this->filename()),
                $this->filesystem->files($this->filename())
            )
        );
    }

    public function allFiles(): FileCollection
    {
        return $this->collect(
            $this->filesystem->allFiles($this->filename())
        );
    }

    protected function collect(array $filenames): FileCollection
    {
        foreach ($filenames as $i => $filename) {
            $filenames[$i] = self::hydrate($filename);
        }

        return FileCollection::make($filenames);
    }

    public function toArray()
    {
        return parent::toArray() + [
                'dir' => true,
                'file' => false
            ];
    }

    public function glob(string $pattern, int $flags = 0): FileCollection
    {
        return $this->collect(
            $this->filesystem->glob($this->filename() . DIRECTORY_SEPARATOR . $pattern, $flags)
        );
    }
}