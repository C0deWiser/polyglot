<?php


namespace Codewiser\Translation\Collectors;


use Codewiser\Translation\Contracts\CollectorInterface;
use Exception;
use Illuminate\Support\Str;
use Sepia\PoParser\Catalog\Entry;
use Sepia\PoParser\Parser;
use Sepia\PoParser\SourceHandler\FileSystem;

class StringsCollector implements CollectorInterface
{
    use CollectorHelper;

    public function __construct(string $base_path, array $locales, string $storage)
    {
        $this->base_path = $base_path;
        $this->locales = $locales;
        $this->storage = $storage;
    }

    /**
     * Populate strings from source.pot to target folder for every locale.
     *
     * @param string $sourcePot
     * @param string $targetDir
     * @param array $locales
     */
    public function populate(string $sourcePot, string $targetDir, array $locales)
    {
        if (!file_exists($sourcePot)) {
            return;
        }

        $parser = new Parser(new FileSystem($sourcePot));
        try {
            $content = $parser->parse();

            foreach ($content->getEntries() as $entry) {
                foreach ($locales as $locale) {

                    if ($this->isPluralEntry($entry)) {
                        // Store plurals
                        continue;
                    }

                    if ($this->isKeyEntry($entry)) {
                        $this->mergeKeyEntry($targetDir, $locale, $entry->getMsgId());
                    } else {
                        $this->mergeStringEntry($targetDir, $locale, $entry->getMsgId());
                    }
                }
            }

        } catch (Exception $e) {

        }
    }

    /**
     * Merge translation string into json file.
     *
     * @param string $target
     * @param string $locale
     * @param string $msgid
     */
    public function mergeStringEntry(string $target, string $locale, string $msgid)
    {
        $path = rtrim($target, DIRECTORY_SEPARATOR);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $filename = $path . DIRECTORY_SEPARATOR . $locale . '.json';

        if (file_exists($filename)) {
            $strings = json_decode(file_get_contents($filename), true);
        } else {
            $strings = [];
        }

        if (!isset($strings[$msgid])) {
            $strings[$msgid] = $msgid;
        }

        file_put_contents($filename, json_encode($strings, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
    }

    /**
     * Merge translation string into php file.
     *
     * @param string $storageDir
     * @param string $locale
     * @param string $msgid
     */
    public function mergeKeyEntry(string $storageDir, string $locale, string $msgid)
    {
        $path = rtrim($storageDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $locale;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $key = explode('.', $msgid);

        $filename = array_shift($key);
        $filename = $path . DIRECTORY_SEPARATOR . $filename . '.php';

        if (file_exists($filename)) {
            $strings = include $filename;
        } else {
            $strings = [];
        }

        $strings = $this->mergeArrayRecursive($strings, implode('.', $key), $msgid);

        $this->saveArrayIntoLangFile($strings, $filename);
    }

    /**
     * Save array to file that may be included.
     *
     * @todo try to format output
     * @param array $strings
     * @param string $filename
     */
    public function saveArrayIntoLangFile(array $strings, string $filename)
    {
        file_put_contents($filename, "<?php\nreturn " . var_export($strings, true) . ';');
    }

    /**
     * Merge msgid into array of strings to the given path.
     *
     * @param array $strings
     * @param string $path
     * @param string $msgid
     * @return array
     */
    public function mergeArrayRecursive(array $strings, string $path, string $msgid)
    {
        $key = explode('.', $path);

        $i = array_shift($key);

        if ($key) {
            // Nested
            if (!isset($strings[$i])) {
                $strings[$i] = [];
            }
            $strings[$i] = $this->mergeArrayRecursive($strings[$i], implode('.', $key), $msgid);
        } elseif (!isset($strings[$i])) {
            $strings[$i] = $msgid;
        }

        return $strings;
    }

    /**
     * Check if given entry is plural.
     *
     * @param Entry $entry
     * @return bool
     */
    protected function isPluralEntry(Entry $entry)
    {
        return ($entry->getMsgId() && $entry->getMsgIdPlural()) ? true : false;
    }

    /**
     * Check if given entry is dot.separated.key.
     *
     * @param Entry $entry
     * @return array|bool
     */
    protected function isKeyEntry(Entry $entry)
    {
        $msgid = $entry->getMsgId();

        if (preg_match('~^\S*$~', $msgid)
            && (Str::lower($msgid) === $msgid)
            && ($key = explode('.', $msgid))
            && (count($key) > 1)) {
            return $key;
        } else {
            return false;
        }
    }

    /**
     * Store parsed strings into given path.
     *
     * @return void
     */
    public function store()
    {
        $this->populate($this->getPortableObjectTemplate(), $this->storage, $this->locales);
    }

}