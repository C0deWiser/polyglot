<?php

namespace Codewiser\Polyglot\Xgettext;

use Codewiser\Polyglot\Contracts\SeparatorContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\PoFileHandler;
use Codewiser\Polyglot\Polyglot;
use Codewiser\Polyglot\Traits\AsSeparator;
use Codewiser\Polyglot\Traits\FilesystemSetup;
use Sepia\PoParser\Parser;
use Sepia\PoParser\PoCompiler;
use Sepia\PoParser\SourceHandler\FileSystem;

/**
 * Separates POT file.
 */
class XgettextSeparator implements SeparatorContract
{
    use FilesystemSetup, AsSeparator;

    public function __construct(string $source = null)
    {
        if ($source) {
            $this->source = new PoFileHandler($source);
        }
    }

    public function separate(): bool
    {
        $separated = false;

        if ($this->source->exists()) {

            $naturalStringsFile = $this->getNaturalStringsFilename();
            $naturalStringsFile->parent()->ensureDirectoryExists();
            $naturalStringsFile->delete();
            $this->source->copyTo($naturalStringsFile);

            if ($catalog = $naturalStringsFile->catalog()) {
                foreach ($catalog->getEntries() as $entry) {
                    if (Polyglot::isDotSeparatedKey($entry->getMsgId())) {
                        // Remove dot.separated.keys
                        $catalog->removeEntry($entry->getMsgId(), $entry->getMsgCtxt());
                    }
                }
                $naturalStringsFile->save($catalog);
                $separated = true;
            }

            $dotSeparatedFile = $this->getDotSeparatedFilename();
            $dotSeparatedFile->parent()->ensureDirectoryExists();
            $dotSeparatedFile->delete();
            $this->source->copyTo($dotSeparatedFile);

            if ($catalog = $dotSeparatedFile->catalog()) {
                foreach ($catalog->getEntries() as $entry) {
                    if (!Polyglot::isDotSeparatedKey($entry->getMsgId())) {
                        // Remove normal strings
                        $catalog->removeEntry($entry->getMsgId(), $entry->getMsgCtxt());
                    }
                }
                $dotSeparatedFile->save($catalog);
                $separated = true;
            }
        }

        return $separated;
    }

    protected function getDotSeparatedFilename(): PoFileHandler
    {
        $filename = $this->temporize(
            $this->source->parent() .
            DIRECTORY_SEPARATOR . class_basename(__METHOD__) .
            DIRECTORY_SEPARATOR . $this->source->basename()
        );

        return new PoFileHandler($filename);
    }

    protected function getNaturalStringsFilename(): PoFileHandler
    {
        // We should keep dir structure
        // .../LC_MESSAGES/messages.pot
        // as we will use parent dir as category
        // and filename as text domain.

        $category = basename($this->source->parent());

        $filename = $this->temporize(
            $this->source->parent() .
            DIRECTORY_SEPARATOR . class_basename(__METHOD__) .
            DIRECTORY_SEPARATOR . $category .
            DIRECTORY_SEPARATOR . $this->source->basename()
        );

        return new PoFileHandler($filename);
    }

    public function getExtractedKeys(): ?FileHandlerContract
    {
        $filename = $this->getDotSeparatedFilename();

        return $filename->exists() ? $filename : null;
    }

    public function getExtractedStrings(): ?FileHandlerContract
    {
        $filename = $this->getNaturalStringsFilename();

        return $filename->exists() ? $filename : null;
    }
}