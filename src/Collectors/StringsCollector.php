<?php


namespace Codewiser\Polyglot\Collectors;


use Codewiser\Polyglot\Contracts\CollectorInterface;
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
        $this->domain = 'default';
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
            $strings[$msgid] = "";
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
     * @param array $strings
     * @param string $filename
     * @todo try to format output
     */
    public function saveArrayIntoLangFile(array $strings, string $filename)
    {
        $content = var_export($strings, true);

//        $content = str_replace('array (', '[', $content);
//        $content = str_replace(');', ']', $content);
//        $content = str_replace('),', ']', $content);

        file_put_contents($filename, "<?php\nreturn " . $content . ';');
    }

    /**
     * Merge msgid into array of strings to the given path.
     *
     * @param array $strings
     * @param string $path
     * @param string $msgid
     * @return array
     */
    public function mergeArrayRecursive(array $strings, string $path, string $msgid): array
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
            $strings[$i] = "";
        }

        return $strings;
    }

    /**
     * Store parsed strings into given path.
     *
     * @param string|null $pot
     * @return void
     */
    public function store(string $pot = null): void
    {
        $this->populate($pot ?: $this->getPortableObjectTemplate(), $this->storage, $this->locales);
    }
}