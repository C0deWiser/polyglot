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

        if ($this->source instanceof JsonFileHandler) {
            $entries = json_decode($this->source->getContent(), true);

            $this->target->putContent(json_encode($entries));
        }

        if ($this->source instanceof PhpFileHandler) {
            $entries = include($this->source);

            $this->target->putContent(json_encode($entries));
        }

        if ($this->source instanceof PoFileHandler) {
            $this->target->putContent(json_encode(
                $this->source->allEntries()
                    ->translated()
                    ->reject(function (Entry $entry) {
                        return $entry->isObsolete();
                    })
                    ->api()
                    ->map(function (array $row) {
                        unset($row['flags']);
                        unset($row['fuzzy']);
                        unset($row['obsolete']);
                        unset($row['reference']);
                        unset($row['developer_comments']);
                        unset($row['comment']);
                        if (!$row['context']) {
                            unset($row['context']);
                        }
                        return $row;
                    })
            ));
        }
    }
}