<?php


namespace Codewiser\Polyglot\Collectors;


use Codewiser\Polyglot\Contracts\CollectorInterface;
use Illuminate\Support\Str;
use Sepia\PoParser\Parser;
use Sepia\PoParser\PoCompiler;
use Sepia\PoParser\SourceHandler\FileSystem;

class GettextCollector implements CollectorInterface
{
    use CollectorHelper;

    /**
     * Store compiled mo in this folder.
     *
     * @var string
     */
    protected string $compiled;

    /**
     * Translate these strings using Translator service.
     *
     * @var array
     */
    protected array $legacy = [];

    public function __construct(string $base_path, array $locales, string $storage, string $domain, string $compile)
    {
        $this->base_path = $base_path;
        $this->locales = $locales;
        $this->storage = $storage;
        $this->domain = $domain;
        $this->compiled = $compile;
    }

    /**
     * Set array of strings, that should be translated by legacy service.
     *
     * @param array $legacy
     * @return $this
     */
    public function setLegacy(array $legacy): GettextCollector
    {
        $this->legacy = $legacy;
        
        return $this;
    }

    public function getPortableObjectTemplate(): string
    {
        if (!file_exists($this->storage)) {
            mkdir($this->storage, 0777, true);
        }

        return $this->storage . DIRECTORY_SEPARATOR . $this->domain . '.pot';
    }

    /**
     * Store parsed strings into given path.
     *
     * @param string|null $pot
     * @return void
     */
    public function store(string $pot = null): void
    {
        $pot = $pot ?: $this->getPortableObjectTemplate();

        if (!file_exists($pot)) {
            return;
        }

        // We should divide collected strings into two parts — legacy and gettext...
        // Then store legacy strings using StringCollector
        $legacyPot = dirname($pot) . DIRECTORY_SEPARATOR . 'legacy' . basename($pot);
        $this->splitPortableObjectTemplate($pot, $legacyPot, $this->legacy);
        if (file_exists($legacyPot)) {
            app(StringsCollector::class)->store($legacyPot);
            unlink($legacyPot);
        }
        
        foreach ($this->locales as $locale) {
            $po = $this->getPortableObject($this->storage, $locale);

            if (!file_exists($po)) {
                $this->runMsgInit($pot, $po, $locale);
            } else {
                $this->runMsgMerge($pot, $po);
            }
        }
    }

    /**
     * Extract legacy strings to the new pot file.
     *
     * @param string $sourcePot
     * @param string $legacyPot
     * @param array $legacyStrings
     * @return void
     */
    public function splitPortableObjectTemplate(string $sourcePot, string $legacyPot, array $legacyStrings): void
    {
        copy($sourcePot, $legacyPot);

        try {
            $file = new FileSystem($sourcePot);
            $parser = new Parser($file);
            $catalog = $parser->parse();

            foreach ($catalog->getEntries() as $entry) {
                if ($this->isKeyEntry($entry) && $this->isLegacy($entry->getMsgId())) {
                    $catalog->removeEntry($entry->getMsgId());
                }
            }

            $file->save((new PoCompiler())->compile($catalog));
        } catch (\Exception $e) {}

        try {
            $file = new FileSystem($legacyPot);
            $parser = new Parser($file);
            $catalog = $parser->parse();

            foreach ($catalog->getEntries() as $entry) {
                if (!$this->isKeyEntry($entry) || !$this->isLegacy($entry->getMsgId())) {
                    $catalog->removeEntry($entry->getMsgId());
                }
            }

            $file->save((new PoCompiler())->compile($catalog));
        } catch (\Exception $e) {}
    }

    protected function isLegacy(string $key): bool
    {
        return Str::startsWith($key, $this->legacy);
    }

    /**
     * Compile MO files.
     */
    public function compile()
    {
        foreach ($this->locales as $locale) {

            $po = $this->getPortableObject($this->storage, $locale);
            $mo = $this->getMachineObject($this->compiled, $locale);

            if (file_exists($po)) {
                $this->runMsgFmt($po, $mo);
            }
        }
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

    protected function runMsgFmt($po, $mo)
    {
        $command = $this->msgfmt . " --use-fuzzy --output-file={$mo} {$po}";
        exec($command);
    }

    /**
     * Get path to PO file.
     *
     * @param string $path
     * @param string $locale
     * @param string $category
     * @return string
     */
    protected function getPortableObject(string $path, string $locale, $category = 'LC_MESSAGES'): string
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR) .
            DIRECTORY_SEPARATOR . $locale .
            DIRECTORY_SEPARATOR . $category;

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        return $path .
            DIRECTORY_SEPARATOR . $this->domain . '.po';
    }

    /**
     * Get path to MO file.
     *
     * @param string $path
     * @param string $locale
     * @param string $category
     * @return string
     */
    protected function getMachineObject(string $path, string $locale, $category = 'LC_MESSAGES'): string
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR) .
            DIRECTORY_SEPARATOR . $locale .
            DIRECTORY_SEPARATOR . $category;

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        return $path .
            DIRECTORY_SEPARATOR . $this->domain . '.mo';
    }
}