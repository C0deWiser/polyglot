<?php

namespace Codewiser\Polyglot\Manipulators;

use Codewiser\Polyglot\Collections\EntryCollection;
use Codewiser\Polyglot\Collections\StringsCollection;
use Codewiser\Polyglot\Contracts\ManipulatorInterface;
use Codewiser\Polyglot\Traits\Manipulator;
use Illuminate\Support\Str;
use Sepia\PoParser\Catalog\Entry;
use Sepia\PoParser\Parser;
use Sepia\PoParser\SourceHandler\FileSystem;
use function collect;

class StringsManipulator implements ManipulatorInterface
{
    use Manipulator;

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
     * Get collected strings from portable object template file.
     *
     * @param string $filename
     * @return EntryCollection|Entry[]
     */
    public function getStringsFromTemplate(string $filename): EntryCollection
    {
        if (!$this->fs->exists($filename)) {
            return new EntryCollection;
        }

        $file = new FileSystem($filename);
        $parser = new Parser($file);
        try {
            $catalog = $parser->parse();
            return EntryCollection::make($catalog->getEntries());
        } catch (\Exception $e) {
            return new EntryCollection;
        }
    }

    /**
     * @inheritDoc
     */
    public function populate(string $template)
    {
        $entries = $this->getStringsFromTemplate($template);

        foreach ($this->locales as $locale) {

            // Json strings
            $strings = $entries->stringKeyed()
                ->mapWithKeys(function (Entry $entry) use ($locale) {
                    list($key, $value) = $this->keyValue($entry);
                    return [$key => $value];
                })
                ->toArray();

            $strings = $this->getJsonStrings($locale)->toArray() + $strings;

            // Save
            $this->saveJson($locale, $strings);

            // Php strings
            $entries->dotKeyed()
                // Group by filename (aka namespace)
                ->mapToGroups(function (Entry $entry) use ($entries) {
                    $path = $entries->hasDotSeparatedKey($entry->getMsgId());
                    $namespace = array_shift($path);
                    return [$namespace => implode('.', $path)];
                })
                // Merge with current
                ->each(function (EntryCollection $entries, $namespace) use ($locale) {
                    $flatten = array_fill_keys($entries->toArray(), '');
                    $flatten = $this->getPhpStrings($locale, $namespace)->toArray() + $flatten;

                    $merged = [];

                    foreach ($flatten as $key => $value) {
                        $merged = $this->mergeKeyIntoArray($merged, explode('.', $key), $value);
                    }

                    // Save
                    $this->savePhp($locale, $namespace, $merged);
                });
        }
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
        if (!$this->fs->exists($this->fs->dirname($filename))) {
            $this->fs->makeDirectory($this->fs->dirname($filename), 0777, true);
        }

        if ($this->fs->exists($filename)) {
            $strings = json_decode($this->fs->get($filename), true);
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

        if (!$this->fs->exists($this->fs->dirname($filename))) {
            $this->fs->makeDirectory($this->fs->dirname($filename), 0777, true);
        }

        if ($strings) {
            $this->fs->put($filename, json_encode($strings, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
        } else {
            // $this->fs->delete($filename);
        }
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

        if (!$this->fs->exists($this->fs->dirname($filename))) {
            $this->fs->makeDirectory($this->fs->dirname($filename), 0777, true);
        }

        if ($this->fs->exists($filename)) {
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

        if (!$this->fs->exists($this->fs->dirname($filename))) {
            $this->fs->makeDirectory($this->fs->dirname($filename), 0777, true);
        }

        if ($strings) {
            $content = var_export($strings, true);
            // todo try to format source code.
            $this->fs->put($filename, "<?php\nreturn " . $content . ';');
        } else {
            // $this->fs->delete($filename);
        }
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

        if (!$this->fs->exists($filename)) {
            return new StringsCollection;
        }

        return StringsCollection::make(
            json_decode($this->fs->get($filename), true)
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
        return $this->fs->glob($this->storage . DIRECTORY_SEPARATOR . '*.json');
    }

    public function getPhpStrings(string $locale, string $namespace): StringsCollection
    {
        $filename = $this->getPhpFile($locale, $namespace);

        if (!$this->fs->exists($filename)) {
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
        return $this->fs->glob($this->storage . DIRECTORY_SEPARATOR .
            $locale . DIRECTORY_SEPARATOR . '*.php');
    }
}