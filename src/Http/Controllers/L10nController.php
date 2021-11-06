<?php

namespace Codewiser\Polyglot\Http\Controllers;

use Codewiser\Polyglot\Collectors\GettextCollector;
use Codewiser\Polyglot\Polyglot;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Sepia\PoParser\Catalog\Entry;
use Sepia\PoParser\Parser;
use Sepia\PoParser\PoCompiler;
use Sepia\PoParser\SourceHandler\FileSystem;

class L10nController extends Controller
{
    protected function locales(): array
    {
        return config('polyglot.locales');
    }

    protected function stringsDir(): string
    {
        return rtrim(config('polyglot.collector.storage'), DIRECTORY_SEPARATOR);
    }

    protected function gettextDir(): string
    {
        return rtrim(config('polyglot.translator.po'), DIRECTORY_SEPARATOR);
    }

    public function index()
    {
        $output = [];

        /** @var GettextCollector $gettext */
        $gettext = app(GettextCollector::class);

        foreach ($this->locales() as $locale) {
            $row = [
                'locale' => $locale
            ];

            $json = $this->stringsDir() . DIRECTORY_SEPARATOR . $locale . '.json';
            $row['json'] = $this->jsonStat($json);

            $stringsDir = $this->stringsDir() . DIRECTORY_SEPARATOR . $locale;
            $pattern = $stringsDir . DIRECTORY_SEPARATOR . '*.php';

            $row['strings'] = [];
            foreach (glob($pattern) as $filename) {
                $row['strings'][] = $this->phpStat($filename);
            }

            $gettextDir = $this->gettextDir() . DIRECTORY_SEPARATOR . $locale;
            $pattern = $gettextDir . DIRECTORY_SEPARATOR . 'LC_*';
            $row['gettext'] = [
                'categories' => []
            ];
            foreach (glob($pattern) as $category) {
                $cat = [
                    'category' => basename($category),
                    'domains' => []
                ];
                $subpattern = $category . DIRECTORY_SEPARATOR . '*.po';
                foreach (glob($subpattern) as $filename) {
                    $cat['domains'][] = ['domain' => basename($filename, '.po')] + $this->poStat($filename);
                }
                $row['gettext']['categories'][] = $cat;
            }

            $output[] = $row;
        }

        return response()->json($output);
    }

    protected function validateJson(string $filename): string
    {
        if (!Str::endsWith($filename, '.json')) {
            abort(404);
        }

        $json = $this->stringsDir() . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($json)) {
            abort(404);
        }

        return $json;
    }

    public function getJson(string $filename)
    {
        $json = $this->validateJson($filename);

        return response()->json(
            json_decode(file_get_contents($json), true)
        );
    }

    public function postJson(Request $request, string $filename)
    {
        $json = $this->validateJson($filename);

        $request->validate([
            'key' => 'required|string',
            'value' => 'present'
        ]);

        $values = json_decode(file_get_contents($json), true);
        $values[$request->get('key')] = (string)$request->get('value');
        file_put_contents($json, json_encode($values, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
    }

    protected function validatePhp(string $locale, string $filename): string
    {
        if (!Str::endsWith($filename, '.php')) {
            abort(404);
        }

        $php = $this->stringsDir() . DIRECTORY_SEPARATOR . $locale .
            DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($php)) {
            abort(404);
        }

        return $php;
    }

    public function getPhp(string $locale, string $filename)
    {
        $php = $this->validatePhp($locale, $filename);

        $data = include $php;

        $data = $this->flatten($data);

        return response()->json($data);
    }

    public function postPhp(Request $request, string $locale, string $filename)
    {
        $php = $this->validatePhp($locale, $filename);

        $request->validate([
            'key' => 'required|string',
            'value' => 'present'
        ]);

        $data = include $php;

        $data = $this->mergeIntoArray($data, explode('.', $request->get('key')), (string)$request->get('value'));

        $content = var_export($data, true);
        file_put_contents($php, "<?php\nreturn " . $content . ';');
    }

    protected function mergeIntoArray(array $array, array $keyPath, $value): array
    {
        $key = array_shift($keyPath);

        if ($keyPath) {
            // dive into
            $array[$key] = $this->mergeIntoArray($array[$key], $keyPath, $value);
        } else {
            $array[$key] = $value;
        }

        return $array;
    }

    protected function validatePo(string $locale, string $category, string $filename): string
    {
        if (!Str::endsWith($filename, '.po')) {
            abort(404);
        }

        $po = $this->gettextDir() .
            DIRECTORY_SEPARATOR . $locale .
            DIRECTORY_SEPARATOR . $category .
            DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($po)) {
            abort(404);
        }

        return $po;
    }

    public function getPo(string $locale, string $category, string $filename)
    {
        $po = $this->validatePo($locale, $category, $filename);

        $parser = new Parser(new FileSystem($po));
        $catalog = $parser->parse();

        $output = [];

        $output['headers'] = collect($catalog->getHeaders())
            ->mapWithKeys(function (string $string) {
                $values = explode(':', $string);
                return [array_shift($values) => trim(implode(':', $values))];
            })
            ->toArray();

        $output['messages'] = [];

        foreach ($catalog->getEntries() as $entry) {
            $row = [];
            $row['msgid'] = $entry->getMsgId();

            if ($entry->isPlural()) {
                $row['msgid_plural'] = $entry->getMsgIdPlural();
                $row['msgstr'] = $entry->getMsgStrPlurals();
            } else {
                $row['msgstr'] = $entry->getMsgStr();
            }

            $row['fuzzy'] = $entry->isFuzzy();
            $row['obsolete'] = $entry->isObsolete();

            // Not actually supported
            $row['context'] = $entry->getMsgCtxt();

            $row['reference'] = $entry->getReference();
            $row['developer_comments'] = $entry->getDeveloperComments();
            $row['translator_comments'] = $entry->getTranslatorComments();

            $output['messages'][] = $row;
        }

        return response()->json($output);
    }

    public function postPo(Request $request, string $locale, string $category, string $filename)
    {
        $po = $this->validatePo($locale, $category, $filename);

        $request->validate([
            'msgid' => 'required|string',
            'msgstr' => 'present',
            'fuzzy' => 'boolean',
            'comment' => 'present|array',
            'comment.*' => 'string'
        ]);

        $file = new FileSystem($po);
        $parser = new Parser($file);
        $catalog = $parser->parse();

        $entry = $catalog->getEntry($request->get('msgid'));
        if (!$entry) {
            $exists = false;
            $entry = new Entry($request->get('msgid'));
            if ($request->has('msgid_plural')) {
                $entry->setMsgIdPlural($request->get('msgid_plural'));
            }
        } else {
            $exists = true;
        }

        if (($exists && $entry->isPlural()) || (!$exists && $request->has('msgid_plural'))) {
            $request->validate([
                'msgid_plural' => 'required|string',
                'msgstr' => 'array|size:' . $catalog->getHeader()->getPluralFormsCount(),
            ]);
            $entry->setMsgStrPlurals(
                collect($request->get('msgstr'))
                    ->map(function ($value) {
                        return (string)$value;
                    })
                    ->toArray()
            );
        } else {
            $entry->setMsgStr((string)$request->get('msgstr'));
        }

        $flags = $entry->getFlags();
        if ($request->get('fuzzy')) {
            $flags[] = 'fuzzy';
        } else {
            $flags = array_diff($flags, ['fuzzy']);
        }
        $entry->setFlags(array_unique($flags));

        $entry->setTranslatorComments($request->get('comment'));

        if (!$exists) {
            $catalog->addEntry($entry);
        }

        $file->save((new PoCompiler())->compile($catalog));

        $this->updatePoHeader($po, 'X-Generator', 'Polyglot ' . Polyglot::getVersion());
        $this->updatePoHeader($po, 'PO-Revision-Date', now()->format('Y-m-d H:i:sO'));
        if ($user = $request->user()) {
            $this->updatePoHeader($po, 'Last-Translator', "{$user->name} <{$user->email}>");
        }
    }

    protected function updatePoHeader(string $filename, $key, $value)
    {
        $content = file_get_contents($filename);
        $content = preg_replace(
            '~^"' . $key . ':.*?"~mi',
            '"' . $key . ': ' . $value . '\n"',
            $content
        );
        file_put_contents($filename, $content);
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

    protected function poStat(string $filename): ?array
    {
        if (file_exists($filename)) {
            $output = [];
            $output['filename'] = basename($filename);

            $file = new FileSystem($filename);
            $parser = new Parser($file);
            try {
                $catalog = $parser->parse();
                $output['count'] = 0;
                $output['empty'] = 0;
                $output['fuzzy'] = 0;

                foreach ($catalog->getEntries() as $entry) {

                    $output['count']++;

                    if ($entry->isPlural()) {
                        if (collect($entry->getMsgStrPlurals())->reject(function ($string) {
                            return $string;
                        })->isNotEmpty()) {
                            $output['empty']++;
                        }
                    } else {
                        if (!$entry->getMsgStr()) {
                            $output['empty']++;
                        }
                    }

                    if ($entry->isFuzzy()) {
                        $output['fuzzy']++;
                    }
                }
            } catch (\Exception $e) {
                $output['error'] = $e->getMessage();
            }
        } else {
            $output = null;
        }

        return $output;
    }

    protected function phpStat(string $filename): ?array
    {
        if (file_exists($filename)) {
            $output = [];
            $output['filename'] = basename($filename);
            $strings = include($filename);
            if (is_array($strings)) {
                $output += $this->stringsStat($strings);
            } else {
                $output['error'] = 'Not an array';
            }
        } else {
            $output = null;
        }

        return $output;
    }

    protected function jsonStat(string $filename): ?array
    {
        if (file_exists($filename)) {
            $output = [];
            $output['filename'] = basename($filename);
            $strings = json_decode(file_get_contents($filename), true);
            if ($strings) {
                $output += $this->stringsStat($strings);
            } else {
                $output['error'] = json_last_error_msg();
            }
        } else {
            $output = null;
        }

        return $output;
    }

    protected function stringsStat(array $strings): array
    {
        $strings = collect($strings)->flatten();

        $data = [];
        $data['count'] = $strings->count();
        $data['empty'] = $strings
            ->filter(function ($string) {
                return !$string;
            })->count();

        return $data;
    }

}