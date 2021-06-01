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
     * @param string $source
     * @param string $target
     * @param array $locales
     */
    public function populate(string $source, string $target, array $locales)
    {
        if (!file_exists($source)) {
            return;
        }

        $parser = new Parser(new FileSystem($source));
        try {
            $content = $parser->parse();

            foreach ($content->getEntries() as $entry) {
                foreach ($locales as $locale) {

                    if ($this->isPluralEntry($entry)) {
                        continue;
                    }

                    if ($this->isKeyEntry($entry)) {
                        $this->mergeKeyEntry($target, $locale, $entry->getMsgId());
                    } else {
                        $this->mergeStringEntry($target, $locale, $entry->getMsgId());
                    }
                }
            }

        } catch (Exception $e) {

        }
    }

    /**
     * Merge translation string into translation folder (e.g. resources/lang).
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

    public function mergeKeyEntry(string $target, string $locale, string $msgid)
    {
        $path = rtrim($target, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $locale;
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

    public function saveArrayIntoLangFile(array $strings, string $filename)
    {
        file_put_contents($filename, '<?php return ' . var_export($strings, true) . ';');
    }

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

    protected function isPluralEntry(Entry $entry)
    {
        return ($entry->getMsgId() && $entry->getMsgIdPlural()) ? true : false;
    }

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