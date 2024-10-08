<?php

namespace Codewiser\Polyglot\Producers;

use Codewiser\Polyglot\Contracts\ProducerContract;
use Codewiser\Polyglot\FileSystem\PoFileHandler;
use Codewiser\Polyglot\Traits\AsProducer;
use Codewiser\Polyglot\Traits\FilesystemSetup;

class ProducerOfPo implements ProducerContract
{
    use FilesystemSetup, AsProducer;

    protected string $msginit = 'msginit';
    protected string $msgmerge = 'msgmerge';

    /**
     * Set msginit executable.
     *
     * @param string $executable
     */
    public function setMsgInitExecutable(string $executable): void
    {
        $this->msginit = $executable;
    }

    /**
     * Set msgmerge executable.
     *
     * @param string $executable
     */
    public function setMsgMergeExecutable(string $executable): void
    {
        $this->msgmerge = $executable;
    }

    public function produce(?array $locales = null): bool
    {
        foreach (($locales ?? $this->locales) as $locale) {
            $category = basename($this->source->parent());
            $text_domain = $this->source->name();

            $output = $this->getOutputFile($locale, $category, $text_domain);

            if ($output->exists()) {
                $this->runMsgMerge($this->source, $output);
            } else {
                $output->parent()->ensureDirectoryExists();
                $this->runMsgInit($this->source, $output, $locale);
            }

            $this->populated->add($output);
        }

        return true;
    }

    public function getOutputFile(string $locale, string $category, string $text_domain): PoFileHandler
    {
        return new PoFileHandler(
            $this->storage .
            DIRECTORY_SEPARATOR . $locale .
            DIRECTORY_SEPARATOR . $category .
            DIRECTORY_SEPARATOR . $text_domain . '.po'
        );
    }

    protected function runMsgInit($pot, $po, $locale)
    {
        $command = $this->msginit . " --no-translator --no-wrap --input={$pot} --output-file={$po} --locale={$locale}";
        exec($command);
    }

    protected function runMsgMerge($pot, $po)
    {
        $command = $this->msgmerge . " --no-wrap --sort-output --update {$po} {$pot}";
        exec($command);
    }
}