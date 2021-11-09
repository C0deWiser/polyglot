<?php

namespace Codewiser\Polyglot;

use Illuminate\Filesystem\Filesystem;

class FileLoader extends \Illuminate\Translation\FileLoader
{
    /**
     * Application base path.
     *
     * @var string
     */
    protected string $base_path;

    /**
     * Path for tmp files.
     *
     * @var string
     */
    protected string $tmp;

    public function __construct(Filesystem $files, $path, $base_path, $tmp)
    {
        parent::__construct($files, $path);

        $this->base_path = $base_path;
        $this->tmp = $tmp;
    }

    /**
     * Get file system handler object.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function filesystem(): \Illuminate\Filesystem\Filesystem
    {
        return $this->files;
    }

    /**
     * Get translation folder.
     *
     * @return string
     */
    public function storage(): string
    {
        return $this->path;
    }

    /**
     * Get app base path.
     *
     * @return string
     */
    public function appPath(): string
    {
        return $this->base_path;
    }

    /**
     * Get tmp path.
     *
     * @return string
     */
    public function tmpPath(): string
    {
        return $this->tmp;
    }
}