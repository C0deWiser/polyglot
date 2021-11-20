<?php

namespace Codewiser\Polyglot\FileSystem;

use Codewiser\Polyglot\Collections\EntryCollection;
use Codewiser\Polyglot\Contracts\EntryCollectionContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\Traits\HasStatistics;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Sepia\PoParser\Catalog\Catalog;
use Sepia\PoParser\Catalog\Entry;
use Sepia\PoParser\Catalog\Header;
use Sepia\PoParser\Parser;
use Sepia\PoParser\PoCompiler;
use Sepia\PoParser\SourceHandler\FileSystem;

class PoFileHandler extends FileHandler implements FileHandlerContract
{
    use HasStatistics;

    public function headers(): Collection
    {
        if ($header = $this->header()) {
            return collect($header->asArray())
                ->map(function (string $header) {
                    $h = explode(':', $header);
                    $key = array_shift($h);
                    $value = trim(implode(':', $h));
                    return [
                        'key' => $key,
                        'value' => $value
                    ];
                });
        }

        return collect();
    }

    public function header(): ?Header
    {
        if ($catalog = $this->catalog()) {
            return $catalog->getHeader();
        }

        return null;
    }

    public function updateHeader(array $headers)
    {
        try {
            $content = $this->getContent();

            foreach ($headers as $key => $value) {
                $content = preg_replace(
                    '~^"' . $key . ':.*?"~mi',
                    '"' . $key . ': ' . $value . '\n"',
                    $content
                );
            }

            return $this->putContent($content);
        } catch (FileNotFoundException $e) {
            return false;
        }
    }

    public function catalog(): ?Catalog
    {
        if (!$this->exists()) {
            return null;
        }

        $file = new FileSystem($this->filename);
        $parser = new Parser($file);

        try {
            return $parser->parse();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function allEntries(): EntryCollectionContract
    {
        if ($catalog = $this->catalog()) {
            return EntryCollection::make($catalog->getEntries());
        }

        return new EntryCollection;
    }

    /**
     * @param array $key [msgid, context]
     * @return Entry|null
     */
    public function getEntry($key): ?Entry
    {
        if ($catalog = $this->catalog()) {
            return $catalog->getEntry($key['msgid'], @$key['context']);
        }

        return null;
    }

    /**
     * @param array $key
     * @param array $value
     * @return bool
     */
    public function putEntry($key, $value): bool
    {
        if ($catalog = $this->catalog()) {

            $entry = $catalog->getEntry($key['msgid'], @$key['context']);

            if (!$entry) {
                // Creating entry
                $entry = new Entry($key['msgid']);
                if (isset($value['msgid_plural'])) {
                    $entry->setMsgIdPlural($value['msgid_plural']);
                }
                if (isset($key['context'])) {
                    $entry->setMsgCtxt($key['context']);
                }
                $catalog->addEntry($entry);
            }

            // Set message string
            if ($entry->getMsgIdPlural()) {
                $entry->setMsgStrPlurals(
                    collect($value['msgstr'])
                        ->map(function ($value) {
                            return (string)$value;
                        })
                        ->toArray()
                );
            } else {
                $entry->setMsgStr((string)$value['msgstr']);
            }

            // Set fuzzy
            $flags = $entry->getFlags();
            if (isset($value['fuzzy']) && $value['fuzzy']) {
                $flags[] = 'fuzzy';
            } else {
                $flags = array_diff($flags, ['fuzzy']);
            }
            $entry->setFlags(array_unique($flags));

            // Set comments
            $entry->setTranslatorComments((array)@$value['comment']);

            return $this->save($catalog);
        }

        return false;
    }

    public function removeEntry($key): bool
    {
        if ($catalog = $this->catalog()) {
            $catalog->removeEntry($key['msgid'], @$key['context']);
            return $this->save($catalog);
        }

        return false;
    }

    public function save(Catalog $catalog): bool
    {
        $file = new FileSystem($this->filename);
        try {
            return $file->save((new PoCompiler())->compile($catalog));
        } catch (\Exception $e) {
            return false;
        }
    }
}