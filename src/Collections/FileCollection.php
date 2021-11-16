<?php

namespace Codewiser\Polyglot\Collections;

use Codewiser\Polyglot\Contracts\StatisticsContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\Contracts\ResourceContract;
use Codewiser\Polyglot\FileSystem\JsonFileHandler;
use Codewiser\Polyglot\FileSystem\PhpFileHandler;
use Codewiser\Polyglot\FileSystem\PoFileHandler;
use Codewiser\Polyglot\FileSystem\ResourceHandler;
use Codewiser\Polyglot\Statistics;
use Illuminate\Support\Carbon;

/**
 * @method ResourceContract first(callable $callback = null, $default = null)
 */
class FileCollection extends \Illuminate\Support\Collection
{
    public function lastModified(): ?Carbon
    {
        $latest = $this
            ->sortByDesc(function ($filename) {
                return filemtime($filename);
            })
            ->first();

        return $latest ? Carbon::createFromTimestamp($latest->lastModified()) : null;
    }

    public function translatable(): FileCollection
    {
        return $this
            ->filter(function (ResourceContract $resource) {
                return $resource instanceof FileHandlerContract;
            });
    }

    public function json(): FileCollection
    {
        return $this
            ->filter(function (ResourceContract $resource) {
                return $resource instanceof JsonFileHandler;
            });
    }

    public function php(): FileCollection
    {
        return $this
            ->filter(function (ResourceContract $resource) {
                return $resource instanceof PhpFileHandler;
            });
    }

    public function po(): FileCollection
    {
        return $this
            ->filter(function (ResourceContract $resource) {
                return $resource instanceof PoFileHandler;
            });
    }

    public function mo(): FileCollection
    {
        return $this
            ->filter(function (ResourceContract $resource) {
                if ($resource instanceof FileContract) {
                    return $resource->extension() == 'mo';
                }
                return false;
            });
    }

    public function statistics(): StatisticsContract
    {
        $statistics = new Statistics();

        $this
            ->translatable()
            ->each(function (ResourceContract $resource) use (&$statistics) {
                $statistics->add($resource->allEntries());
            });

        return $statistics;
    }

    public function makeRelatedTo(string $path): FileCollection
    {
        return $this->map(function (ResourceContract $resource) use ($path) {
            $resource->relatedTo($path);
            return $resource;
        });
    }
}