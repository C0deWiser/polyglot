<?php

namespace Codewiser\Polyglot\FileSystem;

use Codewiser\Polyglot\Collections\StringCollection;
use Codewiser\Polyglot\Contracts\EntryCollectionContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\Traits\HasStatistics;
use Codewiser\Polyglot\FileSystem\Traits\KeyValueFileHandler;
use Sepia\PoParser\Catalog\Entry;

class JsonFileHandler extends FileHandler implements FileHandlerContract
{
    use KeyValueFileHandler, HasStatistics;

    public function allEntries(): EntryCollectionContract
    {
        $collection = new StringCollection();

        if (!$this->exists()) {
            return $collection;
        }

        $strings = json_decode($this->filesystem->get($this), true);

        if (!is_array($strings)) {
            return $collection;
        }

        foreach ($strings as $key => $value) {
            $collection->add(new Entry($key, $value));
        }

        return $collection;
    }

    /**
     * Append new entries.
     *
     * @param EntryCollectionContract $entries
     * @return bool
     */
    public function append(EntryCollectionContract $entries): bool
    {
        $strings = $this->allEntries();

        $entries->each(function (Entry $entry) use ($strings) {
            if (!$strings->exists($entry)) {
                $strings->add($entry);
            }
        });

        return $this->save($strings->sort());
    }

    public function save(EntryCollectionContract $entries): bool
    {
        $json = $entries
            ->mapWithKeys(function (Entry $entry) {
                return [$entry->getMsgId() => $entry->getMsgStr()];
            })
            ->toArray();

        $this->parent()->ensureDirectoryExists();

        return $this->filesystem->put(
            $this,
            json_encode($json, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT)
        );
    }
}
