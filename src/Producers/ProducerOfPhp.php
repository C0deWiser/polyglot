<?php

namespace Codewiser\Polyglot\Producers;

use Codewiser\Polyglot\Collections\EntryCollection;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\Contracts\ProducerContract;
use Codewiser\Polyglot\FileSystem\PhpFileHandler;
use Codewiser\Polyglot\Polyglot;
use Codewiser\Polyglot\Traits\AsProducer;
use Codewiser\Polyglot\Traits\FilesystemSetup;
use Sepia\PoParser\Catalog\Entry;

/**
 * Create PHP files from POT source.
 *
 * Awaits dot.separated.keys in source file.
 */
class ProducerOfPhp implements ProducerContract
{
    use FilesystemSetup, AsProducer;

    public function produce(): bool
    {
        foreach ($this->locales as $locale) {

            $this->source->allEntries()
                // Group extracted strings by first key segment
                ->mapToGroups(function (Entry $entry) {
                    $path = $this->getKeyPath($entry->getMsgId());
                    $group = array_shift($path);
                    $entry->setMsgId(implode('.', $path));
                    return [$group => $entry];
                })

                // Append to current
                ->each(function (EntryCollection $entries, $group) use ($locale) {

                    $output = $this->getOutputFile($locale, $group);

                    $appended = $output->append($entries);

                    if ($appended) {
                        $this->populated->add($output);
                    }
                });
        }

        return true;
    }

    protected function getOutputFile(string $locale, string $group): PhpFileHandler
    {
        return new PhpFileHandler(
            $this->storage .
            DIRECTORY_SEPARATOR . $locale .
            DIRECTORY_SEPARATOR . $group . '.php'
        );
    }

    /**
     * Get message key as path.
     *
     * @param string $msgid
     * @return array|null
     */
    protected function getKeyPath(string $msgid): ?array
    {
        if (Polyglot::isDotSeparatedKey($msgid)) {
            $keys = explode('::', $msgid);

            $keys = $keys[count($keys) - 1];

            return explode('.', $keys);
        } else {
            return null;
        }
    }
}