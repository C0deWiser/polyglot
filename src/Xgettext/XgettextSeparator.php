<?php

namespace Codewiser\Polyglot\Xgettext;

use Codewiser\Polyglot\Contracts\SeparatorContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\PoFileHandler;
use Codewiser\Polyglot\Polyglot;
use Codewiser\Polyglot\Traits\AsSeparator;
use Codewiser\Polyglot\Traits\FilesystemSetup;

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
        return $this->getFilename(class_basename(__CLASS__) . DIRECTORY_SEPARATOR . 'dotkeys');
    }

    protected function getNaturalStringsFilename(): PoFileHandler
    {
        return $this->getFilename(class_basename(__CLASS__) . DIRECTORY_SEPARATOR . 'natural');
    }

    protected function getFilename(string $sub_path):PoFileHandler
    {
        // We should keep dir structure
        // .../LC_MESSAGES/messages.pot
        // as we will use parent dir as category
        // and filename as text domain.

        $category = basename($this->source->parent());

        $filename = $this->temporize(
            $this->source->parent()->parent()->parent() .
            DIRECTORY_SEPARATOR . $sub_path .
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
