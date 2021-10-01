<?php


namespace Codewiser\Translation\Collectors;


use Codewiser\Translation\Contracts\CollectorInterface;

class GettextCollector implements CollectorInterface
{
    use CollectorHelper;

    /**
     * Translation domain used for naming files.
     *
     * @var string
     */
    protected $domain;

    /**
     * Store compiled mo in this folder.
     *
     * @var string
     */
    protected $compiled;

    public function __construct(string $base_path, array $locales, string $storage, string $domain, string $compile)
    {
        $this->base_path = $base_path;
        $this->locales = $locales;
        $this->storage = $storage;
        $this->domain = $domain;
        $this->compiled = $compile;
    }

    /**
     * Store parsed strings into given path.
     *
     * @return void
     */
    public function store()
    {
        foreach ($this->locales as $locale) {

            $pot = $this->getPortableObjectTemplate();
            $po = $this->getPortableObject($this->storage, $locale);

            if (file_exists($pot)) {
                if (!file_exists($po)) {
                    $this->runMsgInit($pot, $po, $locale);
                } else {
                    $this->runMsgMerge($pot, $po);
                }
            }
        }
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
    protected function getPortableObject(string $path, string $locale, $category = 'LC_MESSAGES')
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
    protected function getMachineObject(string $path, string $locale, $category = 'LC_MESSAGES')
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