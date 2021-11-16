<?php

namespace Codewiser\Polyglot\FileSystem;

use Codewiser\Polyglot\FileSystem\Contracts\DirectoryContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileContract;
use Codewiser\Polyglot\FileSystem\Contracts\ResourceContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class ResourceHandler implements ResourceContract
{
    protected string $filename;
    protected Filesystem $filesystem;

    protected string $related = '';

    public static function hydrate(string $filename): ResourceContract
    {
        $mock = new static('/');

        if ($mock->filesystem->isDirectory($filename)) {
            $file = new DirectoryHandler($filename);
        } else {
            if ($filename instanceof SplFileInfo) {
                $filename = $filename->getPathname();
            }
            switch ($mock->filesystem->extension($filename)) {
                case 'json':
                    $file = new JsonFileHandler($filename);
                    break;
                case 'php':
                    $file = new PhpFileHandler($filename);
                    break;
                case 'po':
                case 'pot':
                    $file = new PoFileHandler($filename);
                    break;
                default:
                    $file = new FileHandler($filename);
                    break;
            }
        }

        return $file;
    }

    public function __construct(string $filename)
    {
        $this->filename = rtrim($filename, DIRECTORY_SEPARATOR);
        $this->filesystem = new Filesystem();
    }

    public function __toString()
    {
        return $this->filename;
    }

    public function relatedTo(string $path)
    {
        $this->related = rtrim($path, DIRECTORY_SEPARATOR);
    }

    public function parent(): DirectoryContract
    {
        return new DirectoryHandler(
            $this->filesystem->dirname($this)
        );
    }

    public function basename(): string
    {
        return $this->filesystem->basename($this);
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function exists(): bool
    {
        return $this->filesystem->exists($this);
    }

    public function lastModified(): ?int
    {
        return $this->filesystem->lastModified($this) ?: null;
    }

    public function toArray()
    {
        return [
            'filename' => $this->filename(),
            'relative' => trim(
                Str::replace($this->related, '', $this->filename()),
                DIRECTORY_SEPARATOR
            ),
            'basename' => $this->basename(),
            'lastModified' => $this->lastModified()
        ];
    }

    public function delete(): bool
    {
        if ($this->asDirectory()) {
            return $this->asDirectory()->delete();
        } else {
            return $this->asFile()->delete();
        }
    }

    public function asDirectory(): ?DirectoryContract
    {
        return $this->filesystem->isDirectory($this) ? new DirectoryHandler($this) : null;
    }

    public function asFile(): ?FileContract
    {
        return $this->filesystem->isFile($this) ? new FileHandler($this) : null;
    }
}