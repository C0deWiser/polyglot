<?php

namespace Codewiser\Polyglot\FileSystem\Traits;

use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Sepia\PoParser\Catalog\Entry;

trait KeyValueFileHandler
{
    public function getEntry($key): ?Entry
    {
        return $this->allEntries()
            ->first(function (Entry $entry) use ($key) {
                return $entry->getMsgId() == $key;
            });
    }

    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function putEntry($key, $value): bool
    {
        $strings = $this->allEntries();

        if ($this->getEntry($key)) {
            $strings = $strings->map(function (Entry $entry) use ($key, $value) {
                if ($key == $entry->getMsgId()) {
                    $entry->setMsgStr($value);
                }

                return $entry;
            });
        } else {
            $strings->add(new Entry($key, $value));
        }

        return $this->save($strings);
    }

    public function removeEntry($key): bool
    {
        $strings = $this->allEntries()
            ->reject(function (Entry $entry) use ($key) {
                return $entry->getMsgId() == $key;
            });

        return $this->save($strings);
    }
}