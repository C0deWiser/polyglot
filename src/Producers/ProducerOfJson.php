<?php

namespace Codewiser\Polyglot\Producers;

use Codewiser\Polyglot\Contracts;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\JsonFileHandler;
use Codewiser\Polyglot\Traits\AsProducer;
use Codewiser\Polyglot\Traits\FilesystemSetup;
use Sepia\PoParser\Catalog\Entry;

/**
 * Create JSON files from POT source.
 *
 * Awaits 'Natural Strings' as keys and values.
 */
class ProducerOfJson implements Contracts\ProducerContract
{
    use FilesystemSetup, AsProducer;

    public function produce(?array $locales = null): bool
    {
        foreach (($locales ?? $this->locales) as $locale) {

            $output = $this->getOutputFile($locale);

            $appended = $output->append($this->source->allEntries());

            if ($appended) {
                $this->populated->add($output);
            }
        }

        return true;
    }

    protected function getOutputFile(string $locale): JsonFileHandler
    {
        return new JsonFileHandler(
            $this->storage .
            DIRECTORY_SEPARATOR . $locale . '.json'
        );
    }
}