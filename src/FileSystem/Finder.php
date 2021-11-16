<?php

namespace Codewiser\Polyglot\FileSystem;

use Codewiser\Polyglot\Collections\FileCollection;
use Codewiser\Polyglot\FileSystem\Contracts\DirectoryContract;
use Codewiser\Polyglot\FileSystem\Contracts\FinderContract;
use Codewiser\Polyglot\FileSystem\Contracts\ResourceContract;
use Codewiser\Polyglot\Traits\FilesystemSetup;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;
use Tests\FileHandlerTest\PoFileHandlerTest;

class Finder implements FinderContract
{
    use FilesystemSetup;

    protected DirectoryContract $root_path;
    protected ResourceContract $path;

    public function __construct(string $base_dir, Filesystem $filesystem)
    {
        $this->setRoot($base_dir);
        $this->setFilesystem($filesystem);
        $this->setPath('/');
    }

    public function setRoot(string $base_dir)
    {
        $this->root_path = new DirectoryHandler(
            rtrim($base_dir, DIRECTORY_SEPARATOR)
        );
    }

    public function setPath(string $path)
    {
        // Prevent leaving base dir
        $path = Str::replace('..' . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
        $path = Str::replace($this->root_path, '', $path);
        $path = trim($path, DIRECTORY_SEPARATOR);

        $this->path = ResourceHandler::hydrate(
            $this->root_path . ($path ? DIRECTORY_SEPARATOR . $path : '')
        );

        $this->path->relatedTo($this->root_path);
    }

    public function isRoot(): bool
    {
        return $this->root_path->filename() == $this->path->filename();
    }

    public function getPath(): ResourceContract
    {
        return $this->path;
    }

    public function allFiles(): FileCollection
    {
        if ($this->path instanceof DirectoryContract) {
            return $this->path->allFiles()
                ->makeRelatedTo($this->root_path);
        } else {
            return new FileCollection();
        }
    }

    public function files(): FileCollection
    {
        if ($this->path instanceof DirectoryContract) {
            return $this->path->files()
                ->makeRelatedTo($this->root_path);
        } else {
            return new FileCollection();
        }
    }

    public function parent(): ?DirectoryContract
    {
        if ($this->isRoot()) {
            return null;
        }

        $parent = $this->path->parent();
        $parent->relatedTo($this->root_path);

        return $parent;
    }
}