<?php

namespace Codewiser\Polyglot\Xgettext;

use Codewiser\Polyglot\Contracts\CompilerContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\FileHandler;
use Codewiser\Polyglot\FileSystem\JsonFileHandler;
use Codewiser\Polyglot\FileSystem\PhpFileHandler;
use Codewiser\Polyglot\FileSystem\PoFileHandler;
use Codewiser\Polyglot\Traits\FilesystemSetup;
use Sepia\PoParser\Catalog\Entry;

class JsonCompiler implements CompilerContract
{
    use FilesystemSetup;

    protected FileHandlerContract $source;
    protected FileContract $target;

    public function setSource($source): void
    {
        $this->source = $source;
    }

    public function setTarget($target): void
    {
        $this->target = new FileHandler($target);
    }

    public function compile()
    {
        $this->target->parent()->ensureDirectoryExists();

        if ($this->source instanceof JsonFileHandler ||
            $this->source instanceof PhpFileHandler) {
            $entries = $this->source->allEntries()
                ->mapWithKeys(function (Entry $entry) {
                    return [$entry->getMsgId() => $entry->getMsgStr()];
                })
                ->toArray();

            $this->target->putContent(json_encode($entries));
        }

        if ($this->source instanceof PoFileHandler) {
            $entries = $this->source->allEntries()
                ->reject(function (Entry $entry) {
                    return $entry->isObsolete();
                })
                ->mapWithKeys(function (Entry $entry) {
                    if ($entry->isPlural()) {
                        return [$entry->getMsgId() . '|' . $entry->getMsgIdPlural() => $entry->getMsgStrPlurals()];
                    } else {
                        return [$entry->getMsgId() => $entry->getMsgStr()];
                    }
                })
                ->toArray();

            $this->target->putContent(json_encode($entries));
        }
    }
}