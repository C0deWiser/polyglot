<?php

namespace Codewiser\Polyglot\FileSystem;

use Codewiser\Polyglot\Collections\EntryCollection;
use Codewiser\Polyglot\Collections\StringCollection;
use Codewiser\Polyglot\Contracts\EntryCollectionContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\Traits\HasStatistics;
use Codewiser\Polyglot\FileSystem\Traits\KeyValueFileHandler;
use Sepia\PoParser\Catalog\Entry;

class PhpFileHandler extends FileHandler implements FileHandlerContract
{
    use  KeyValueFileHandler, HasStatistics;

    public function allEntries(): EntryCollectionContract
    {
        $collection = new StringCollection();

        if (!$this->exists()) {
            return new EntryCollection;
        }

        $data = include $this->filename;

        if (!is_array($data)) {
            return $collection;
        }

        foreach ($this->flat($data) as $key => $value) {
            $collection->add(new Entry($key, $value));
        }

        return $collection;
    }

    /**
     * Convert multi-level array to flatten with dot.separated.keys
     *
     * @param array $rows
     * @return array
     */
    public function flat(array $rows): array
    {
        $flatten = [];

        foreach ($rows as $key => $value) {
            if (is_array($value)) {
                foreach ($this->flat($value) as $subkey => $subvalue) {
                    $flatten[$key . '.' . $subkey] = $subvalue;
                }
            } else {
                $flatten[$key] = $value;
            }
        }

        return $flatten;
    }

    /**
     * Convert flat array to multi level.
     *
     * @param EntryCollectionContract $entries
     * @return array
     */
    public function arch(EntryCollectionContract $entries):array
    {
        $flatten = $entries
            ->mapWithKeys(function (Entry $entry) {
                return [$entry->getMsgId() => $entry->getMsgStr()];
            })
        ->toArray();

        $merged = [];

        foreach ($flatten as $key => $value) {
            $merged = $this->mergeKeyIntoArray($merged, explode('.', $key), $value);
        }

        return $merged;
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
        $this->parent()->ensureDirectoryExists();

        $content = var_export($this->arch($entries), true);

        // todo try to format source code.

        return $this->filesystem->put(
            $this,
            "<?php\nreturn " . $content . ';'
        );
    }

    /**
     * Merge string into array of strings to the given path.
     *
     * @param array $strings
     * @param array $keyPath
     * @param string|null $value
     * @return array
     */
    public function mergeKeyIntoArray(array $strings, array $keyPath, string $value = null): array
    {
        $key = array_shift($keyPath);

        if ($keyPath) {
            // dive into
            $strings[$key] = $this->mergeKeyIntoArray((array)@$strings[$key], $keyPath, $value);
        } else {
            if (is_null($value)) {
                // create if not exists
                if (!isset($strings[$key])) {
                    $strings[$key] = '';
                }
            } else {
                // insert|update value
                $strings[$key] = $value;
            }
        }

        return $strings;
    }
}
