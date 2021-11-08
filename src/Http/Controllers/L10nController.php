<?php

namespace Codewiser\Polyglot\Http\Controllers;

use Codewiser\Polyglot\GettextPopulator;
use Codewiser\Polyglot\Polyglot;
use Codewiser\Polyglot\StringsPopulator;
use Illuminate\Http\Request;
use Sepia\PoParser\Catalog\Entry;

class L10nController extends Controller
{
    protected StringsPopulator $strings;
    protected GettextPopulator $gettext;

    public function __construct()
    {
        parent::__construct();

        $this->strings = app(StringsPopulator::class);
        $this->gettext = app(GettextPopulator::class);
    }

    public function index()
    {
        $output = [];

        foreach ($this->strings->getLocales() as $locale) {
            $row = [
                'locale' => $locale
            ];

            $row['json'] = $this->statJson($locale);

            $row['strings'] = [];
            foreach ($this->strings->getPhpListing($locale) as $filename) {
                $row['strings'][] = $this->statPhp($locale, basename($filename, '.php'));
            }

            $row['gettext'] = [
                'categories' => []
            ];
            foreach ($this->gettext->getCategoryListing($locale) as $category) {
                $cat = [
                    'category' => basename($category),
                    'domains' => []
                ];
                foreach ($this->gettext->getPortableObjectListing($locale, $category) as $filename) {
                    $cat['domains'][] = $this->statPo($locale, $category, basename($filename, '.po'));
                }
                $row['gettext']['categories'][] = $cat;
            }

            $output[] = $row;
        }

        return response()->json($output);
    }

    protected function validateJson(string $locale): string
    {
        $json = $this->strings->getJsonFile($locale);

        if (!file_exists($json)) {
            abort(404);
        }

        return $json;
    }

    public function getJson(string $locale)
    {
        $this->validateJson($locale);

        return response()->json(
            $this->strings->getJsonStrings($locale)
        );
    }

    public function postJson(Request $request, string $locale)
    {
        $this->validateJson($locale);

        $request->validate([
            'key' => 'required|string',
            'value' => 'present'
        ]);


        $this->strings->put(
            $locale,
            (string)$request->get('key'),
            (string)$request->get('value')
        );
    }

    protected function validatePhp(string $locale, string $namespace): string
    {
        $php = $this->strings->getPhpFile($locale, $namespace);

        if (!file_exists($php)) {
            abort(404);
        }

        return $php;
    }

    public function getPhp(string $locale, string $namespace)
    {
        $this->validatePhp($locale, $namespace);

        return response()->json(
            $this->strings->getPhpStrings($locale, $namespace)
        );
    }

    public function postPhp(Request $request, string $locale, string $namespace)
    {
        $this->validatePhp($locale, $namespace);

        $request->validate([
            'key' => 'required|string',
            'value' => 'present'
        ]);

        $this->strings->put(
            $locale,
            $namespace . '.' . $request->get('key'),
            (string)$request->get('value')
        );
    }

    protected function validatePo(string $locale, string $category, string $domain): string
    {
        $filename = $this->gettext->getPortableObject($locale, $category, $domain);

        if (!file_exists($filename)) {
            abort(404);
        }

        return $filename;
    }

    public function getPo(string $locale, string $category, string $domain)
    {
        $this->validatePo($locale, $category, $domain);

        $output = [];

        $output['headers'] = $this->gettext->getHeaders($locale, $category, $domain);

        $output['messages'] = $this->gettext->getStrings($locale, $category, $domain)
            ->map(function (Entry $entry) {
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

                return $row;
            })
            ->values();

        return response()->json($output);
    }

    public function postPo(Request $request, string $locale, string $category, string $domain)
    {
        $this->validatePo($locale, $category, $domain);

        $rules = [
            'msgid' => 'required|string',
            'msgstr' => 'present',
            'fuzzy' => 'boolean',
            'comment' => 'present|array',
            'comment.*' => 'string',
            'context' => 'string'
        ];

        $entry = $this->gettext->get($locale, $category, $domain, $request->get('msgid'), $request->get('context'));

        if (($entry && $entry->isPlural()) || (!$entry && $request->has('msgid_plural'))) {
            $rules['msgid_plural'] = 'required|string';
            $rules['msgstr'] = 'array|size:' . $this->gettext
                    ->getHeader($locale, $category, $domain)
                    ->getPluralFormsCount();
        }

        $validated = $request->validate($rules);

        $this->gettext->put($locale, $category, $domain, $validated);

        $this->gettext->updateHeader($locale, $category, $domain, 'X-Generator', 'Polyglot ' . Polyglot::version());
        $this->gettext->updateHeader($locale, $category, $domain, 'PO-Revision-Date', now()->format('Y-m-d H:i:sO'));
        if ($user = $request->user()) {
            $this->gettext->updateHeader($locale, $category, $domain, 'Last-Translator', "{$user->name} <{$user->email}>");
        }
    }

    protected function statPo(string $locale, string $category, string $domain): ?array
    {
        $filename = $this->gettext->getPortableObject($locale, $category, $domain);
        if (file_exists($filename)) {
            $output = [];
            $output['domain'] = $domain;
            $output['filename'] = basename($filename);

            $strings = $this->gettext->getStrings($locale, $category, $domain);
            $output['count'] = $strings->count();
            $output['empty'] = $strings->untranslated()->count();
            $output['fuzzy'] = $strings->fuzzy()->count();
        } else {
            $output = null;
        }

        return $output;
    }

    protected function statPhp(string $locale, string $namespace): ?array
    {
        $filename = $this->strings->getPhpFile($locale, $namespace);

        if (file_exists($filename)) {
            $output = [];
            $output['namespace'] = basename($namespace);
            $output['filename'] = basename($filename);

            $strings = $this->strings->getPhpStrings($locale, $namespace);
            $output['count'] = $strings->flatten()->count();
            $output['empty'] = $strings->flatten()->untranslated()->count();
        } else {
            $output = null;
        }

        return $output;
    }

    protected function statJson(string $locale): ?array
    {
        $filename = $this->strings->getJsonFile($locale);

        if (file_exists($filename)) {
            $output = [];
            $output['filename'] = basename($filename);

            $strings = $this->strings->getJsonStrings($locale);
            $output['count'] = $strings->flatten()->count();
            $output['empty'] = $strings->flatten()->untranslated()->count();
        } else {
            $output = null;
        }

        return $output;
    }

}