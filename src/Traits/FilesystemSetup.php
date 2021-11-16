<?php

namespace Codewiser\Polyglot\Traits;

use Codewiser\Polyglot\FileSystem\Contracts\FileContract;
use Codewiser\Polyglot\FileSystem\FileHandler;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

trait FilesystemSetup
{
    protected string $base_path;
    protected string $temp_path;
    protected Filesystem $filesystem;

    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function setBasePath(string $base_path)
    {
        $this->base_path = $base_path;
    }

    public function setTempPath(string $temp_path)
    {
        $this->temp_path = $temp_path;

        $this->filesystem->ensureDirectoryExists($temp_path);
    }

    /**
     * Get path to temporary copy of given file.
     *
     * @param FileContract|string $filename
     * @return FileContract
     */
    protected function temporize($filename): FileContract
    {
        $relativePathToFile = Str::replace($this->base_path, '', $filename);
        return new FileHandler($this->temp_path . $relativePathToFile);
    }
}