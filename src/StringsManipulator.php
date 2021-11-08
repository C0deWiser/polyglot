<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Contracts\ManipulatorInterface;
use Illuminate\Support\Str;
use Sepia\PoParser\Catalog\Entry;

class StringsManipulator implements ManipulatorInterface
{
    /**
     * Path to lang folder.
     *
     * @var string
     */
    protected string $storage;

    protected array $locales;

    protected StringsCollector $collector;

    public function __construct(array $locales, string $storage, StringsCollector $collector)
    {
        $this->storage = $storage;
        $this->locales = $locales;
        $this->collector = $collector;
    }

    public function getStorage(): string
    {
        return $this->storage;
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * Insert|update entry value to the lang file.
     *
     * @param string $locale
     * @param string $key
     * @param string $value
     */
    public function put(string $locale, string $key, string $value)
    {
        if ($path = $this->isDotKey($key)) {
            $this->mergeDotKeyEntry($locale, $path, $value);
        } else {
            $this->mergeStringEntry($locale, $key, $value);
        }
    }

    /**
     * Create new entry key to the lang file.
     *
     * @param string $locale
     * @param Entry $entry
     * @return string|array
     */
    protected function initEntry(string $locale, Entry $entry)
    {
        if ($path = $this->isDotKey($entry->getMsgId())) {
            $this->mergeDotKeyEntry($locale, $path, null);
            return $path;
        } else {
            list($key) = $this->keyValue($entry);
            $this->mergeStringEntry($locale, $key, null);
            return $key;
        }
    }

    protected function keyValue(Entry $entry): array
    {
        if ($entry->isPlural()) {
            // String|Strings
            $key = $entry->getMsgId() . '|' . $entry->getMsgIdPlural();
            // Translation|Translations
            $value = collect($entry->getMsgStrPlurals())
                ->filter()
                ->join('|');
        } else {
            // String
            $key = $entry->getMsgId();
            // Translation
            $value = $entry->getMsgStr();
        }

        return [$key, $value];
    }

    /**
     * Populate entries from given .pot file to translation files through every given locale.
     * It will not modify translations, just add new entries.
     *
     * @param string $pot
     */
    public function populate(string $pot)
    {
        $entries = $this->collector->getStrings($pot);

        foreach ($this->locales as $locale) {

            // Json strings
            $strings = $entries->stringKeyed()
                ->mapWithKeys(function (Entry $entry) use ($locale) {
                    list($key, $value) = $this->keyValue($entry);
                    return [$key => $value];
                })
                ->toArray();

            // Merge with current
            $this->getJsonStrings($locale)
                ->each(function ($value, $key) use (&$strings) {
                    // Merge old values into new strings
                    if (isset($strings[$key])) $strings[$key] = $value;
                });

            // Save
            $this->saveJson($locale, $strings);

            // Php strings
            $affectedNamespaces = $entries->dotKeyed()
                // Group by filename (aka namespace)
                ->mapToGroups(function (Entry $entry) use ($entries) {
                    $path = $entries->hasDotSeparatedKey($entry->getMsgId());
                    $namespace = array_shift($path);
                    return [$namespace => implode('.', $path)];
                })
                // Merge with current
                ->each(function (EntryCollection $entries, $namespace) use ($locale) {
                    $flatten = array_fill_keys($entries->toArray(), '');
                    $merged = [];

                    foreach ($flatten as $key => $value) {
                        $merged = $this->mergeKeyIntoArray($merged, explode('.', $key), $value);
                    }

                    $this->getPhpStrings($locale, $namespace)
                        ->each(function ($value, $key) use ($flatten, &$merged) {
                            // Merge old values into new strings
                            if (isset($flatten[$key]))
                                $merged = $this->mergeKeyIntoArray($merged, explode('.', $key), $value);
                        });

                    // Save
                    $this->savePhp($locale, $namespace, $merged);
                })
                ->keys()
                ->toArray();

            // Delete obsolete php files?
//            foreach ($this->getPhpListing($locale) as $filename) {
//                $namespace = basename($filename, '.php');
//                if (!in_array($namespace, $affectedNamespaces)) {
//                    unlink($filename);
//                }
//            }
        }
    }

    protected function filterOutPhp(string $locale, string $namespace, array $keys)
    {
        $filtered = [];

        foreach ($this->getPhpStrings($locale, $namespace) as $key => $value) {
            if (in_array($key, $keys)) {
                $filtered = $this->mergeKeyIntoArray($filtered, explode('.', $key), $value);
            }
        }

        $this->savePhp($locale, $namespace, $filtered);
    }

    /**
     * Remove from json keys not in given list.
     *
     * @param string $locale
     * @param array $keys
     */
    protected function filterOutJson(string $locale, array $keys)
    {
        $filtered = $this->getJsonStrings($locale)
            ->filter(function ($string, $key) use ($keys) {
                return in_array($key, $keys);
            });

        $this->saveJson($locale, $filtered->toArray());
    }

    /**
     * Merge key-value to the json file.
     *
     * @param string $locale
     * @param string $key
     * @param string|null $value
     */
    protected function mergeStringEntry(string $locale, string $key, string $value = null)
    {
        $filename = $this->getJsonFile($locale);
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        if (file_exists($filename)) {
            $strings = json_decode(file_get_contents($filename), true);
        } else {
            $strings = [];
        }

        if (is_null($value)) {
            // create if not exists
            if (!isset($strings[$key])) {
                $strings[$key] = '';
            }
        } else {
            // insert|update value
            $strings[$key] = $value;
        }

        $this->saveJson($locale, $strings);
    }

    protected function saveJson(string $locale, array $strings)
    {
        $filename = $this->getJsonFile($locale);

        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        file_put_contents($filename, json_encode($strings, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
    }

    /**
     * Merge key-value to the php file. First path segment is a basename of php file.
     *
     * @param string $locale
     * @param array $path
     * @param string|null $value
     */
    protected function mergeDotKeyEntry(string $locale, array $path, string $value = null)
    {
        $namespace = array_shift($path);
        $filename = $this->getPhpFile($locale, $namespace);

        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        if (file_exists($filename)) {
            $strings = include $filename;
        } else {
            $strings = [];
        }

        $strings = $this->mergeKeyIntoArray($strings, $path, $value);

        $this->savePhp($locale, $namespace, $strings);
    }

    protected function savePhp(string $locale, string $namespace, array $strings)
    {
        $filename = $this->getPhpFile($locale, $namespace);

        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        $content = var_export($strings, true);
        // todo try to format source code.
        file_put_contents($filename, "<?php\nreturn " . $content . ';');
    }

    /**
     * Merge string into array of strings to the given path.
     *
     * @param array $strings
     * @param array $keyPath
     * @param string|null $value
     * @return array
     */
    protected function mergeKeyIntoArray(array $strings, array $keyPath, string $value = null): array
    {
        $key = array_shift($keyPath);

        if ($keyPath) {
            // dive into
            $strings[$key] = $this->mergeKeyIntoArray((array)@$strings[$key], $keyPath, $value);
        } else {
            if (is_null($value)) {
                // create if not exists
                if (!isset($strings[$key])) {
                    $strings[$key] = '';
                }
            } else {
                // insert|update value
                $strings[$key] = $value;
            }
        }

        return $strings;
    }

    /**
     * Check if given entry is dot.separated.key.
     *
     * @param string $msgid
     * @return array|null
     */
    protected function isDotKey(string $msgid): ?array
    {
        if (preg_match('~^\S*$~', $msgid)
            && (Str::lower($msgid) === $msgid)
            && ($key = explode('.', $msgid))
            && (count($key) > 1)) {
            return $key;
        } else {
            return null;
        }
    }

    /**
     * Get strings from json file for the given locale.
     *
     * @param string $locale
     * @return StringsCollection
     */
    public function getJsonStrings(string $locale): StringsCollection
    {
        $filename = $this->getJsonFile($locale);

        if (!file_exists($filename)) {
            return new StringsCollection;
        }

        return StringsCollection::make(
            json_decode(file_get_contents($filename), true)
        );
    }

    /**
     * Get json file for the given locale.
     *
     * @param string $locale
     * @return string
     */
    public function getJsonFile(string $locale): string
    {
        return $this->storage . DIRECTORY_SEPARATOR . $locale . '.json';
    }

    /**
     * Get existing json files.
     *
     * @return array
     */
    public function getJsonListing(): array
    {
        return glob($this->storage . DIRECTORY_SEPARATOR . '*.json');
    }

    public function getPhpStrings(string $locale, string $namespace): StringsCollection
    {
        $filename = $this->getPhpFile($locale, $namespace);

        if (!file_exists($filename)) {
            return new StringsCollection;
        }

        $data = include $filename;

        if (!is_array($data)) {
            return new StringsCollection;
        }

        return StringsCollection::make(
            $this->flatten($data)
        );
    }

    protected function flatten(array $rows): array
    {
        $flatten = [];

        foreach ($rows as $key => $value) {
            if (is_array($value)) {
                foreach ($this->flatten($value) as $subkey => $subvalue) {
                    $flatten[$key . '.' . $subkey] = $subvalue;
                }
            } else {
                $flatten[$key] = $value;
            }
        }

        return $flatten;
    }

    /**
     * Get php file for the given locale and namespace.
     *
     * @param string $locale
     * @param string $namespace
     * @return string
     */
    public function getPhpFile(string $locale, string $namespace): string
    {
        return $this->storage . DIRECTORY_SEPARATOR .
            $locale . DIRECTORY_SEPARATOR . $namespace . '.php';
    }

    /**
     * Get existing php files for given locale.
     *
     * @param string $locale
     * @return array
     */
    public function getPhpListing(string $locale): array
    {
        return glob($this->storage . DIRECTORY_SEPARATOR .
            $locale . DIRECTORY_SEPARATOR . '*.php');
    }
}