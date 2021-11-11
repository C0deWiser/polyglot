<?php

namespace Codewiser\Polyglot\Http\Controllers;

use Codewiser\Polyglot\Manipulators\GettextManipulator;
use Codewiser\Polyglot\Polyglot;
use Codewiser\Polyglot\Manipulators\StringsManipulator;
use Illuminate\Http\Request;
use Sepia\PoParser\Catalog\Entry;

class L10nController extends Controller
{
    protected StringsManipulator $strings;
    protected GettextManipulator $gettext;

    public function __construct()
    {
        parent::__construct();

        $this->strings = app(StringsManipulator::class);
        $this->gettext = app(GettextManipulator::class);
    }

    public function index()
    {
        $output = [];

        $output[0] = ['filename' => 'lang', 'depth' => 4, 'dir' => true, 'level' => 0];

        foreach ($this->strings->getLocales() as $locale) {

            $output['a/' . $locale] = ['filename' => $locale, 'depth' => 3, 'dir' => true, 'level' => 1];

            if ($json = $this->statJson($locale)) {
                $output['z/' . $json['filename']] = $json + ['depth' => 3, 'dir' => false, 'level' => 1];
            }

            foreach ($this->strings->getPhpListing($locale) as $filename) {
                $namespace = basename($filename, '.php');
                $output['a/' . $locale . '/' . $namespace] =
                    $this->statPhp($locale, $namespace) + ['depth' => 2, 'dir' => false, 'level' => 2];
            }
            foreach ($this->gettext->getCategoryListing($locale) as $category) {
                $category = basename($category);
                $output['a/' . $locale . '/' . $category] = ['filename' => $category, 'depth' => 2, 'dir' => true, 'level' => 2];

                foreach ($this->gettext->getPortableObjectListing($locale, $category) as $filename) {
                    $text_domain = basename($filename, '.po');
                    $output['a/' . $locale . '/' . $category . '/' . $text_domain] =
                        $this->statPo($locale, $category, $text_domain) + ['depth' => 1, 'dir' => false, 'level' => 3];
                }
            }
        }

        ksort($output);
        return response()->json(array_values($output));
    }

    protected function validateJson(string $filename): string
    {
        $json = $this->strings->getJsonFile($filename);

        if (!file_exists($json)) {
            abort(404);
        }

        return basename($json, '.json');
    }

    public function getJson(string $filename)
    {
        $locale = $this->validateJson($filename);

        return response()->json(
            $this->strings->getJsonStrings($locale)
                ->api()->values()
        );
    }

    public function postJson(Request $request, string $filename)
    {
        $locale = $this->validateJson($filename);

        $request->validate([
            'msgid' => 'required|string',
            'msgstr' => 'present'
        ]);

        $this->strings->put(
            $locale,
            (string)$request->get('msgid'),
            (string)$request->get('msgstr')
        );
    }

    protected function validatePhp(string $locale, string $filename): string
    {
        $php = $this->strings->getPhpFile($locale, $filename);

        if (!file_exists($php)) {
            abort(404);
        }

        return basename($php, '.php');
    }

    public function getPhp(string $locale, string $filename)
    {
        $namespace = $this->validatePhp($locale, $filename);

        return response()->json(
            $this->strings->getPhpStrings($locale, $namespace)
                ->api()->values()
        );
    }

    public function postPhp(Request $request, string $locale, string $filename)
    {
        $namespace = $this->validatePhp($locale, $filename);

        $request->validate([
            'msgid' => 'required|string',
            'msgstr' => 'present'
        ]);

        $this->strings->put(
            $locale,
            $namespace . '.' . $request->get('msgid'),
            (string)$request->get('msgstr')
        );
    }

    protected function validatePo(string $locale, string $category, string $filename): string
    {
        $po = $this->gettext->getPortableObject($locale, $category, $filename);

        if (!file_exists($po)) {
            abort(404);
        }

        return basename($po, '.po');
    }

    public function getPo(string $locale, string $category, string $filename)
    {
        $text_domain = $this->validatePo($locale, $category, $filename);

        $output = [
            'headers' => $this->gettext
                ->getHeaders($locale, $category, $text_domain),
            'strings' => $this->gettext
                ->getStrings($locale, $category, $text_domain)
                ->api()->values()
        ];

        return response()->json($output);
    }

    public function postPo(Request $request, string $locale, string $category, string $filename)
    {
        $text_domain = $this->validatePo($locale, $category, $filename);

        $rules = [
            'msgid' => 'required|string',
            'msgstr' => 'present',
            'fuzzy' => 'boolean',
            'comment' => 'nullable|string',
            'context' => 'nullable|string'
        ];

        $entry = $this->gettext->get(
            $locale, $category, $text_domain,
            $request->get('msgid'),
            $request->get('context')
        );

        if (($entry && $entry->isPlural()) || (!$entry && $request->has('msgid_plural'))) {
            $rules['msgid_plural'] = 'required|string';
            $rules['msgstr'] = 'array|size:' . $this->gettext
                    ->getHeader($locale, $category, $text_domain)
                    ->getPluralFormsCount();
        }

        $validated = $request->validate($rules);

        $this->gettext->put($locale, $category, $text_domain, $validated);

        $headers = [
            'X-Generator' => 'Polyglot ' . Polyglot::version(),
            'PO-Revision-Date' => now()->format('Y-m-d H:i:sO')
        ];

        if ($user = $request->user()) {
            $headers['Last-Translator'] = "{$user->name} <{$user->email}>";
        }

        $this->gettext->updateHeaders($locale, $category, $text_domain, $headers);
    }

    protected function statPo(string $locale, string $category, string $text_domain): ?array
    {
        $filename = $this->gettext->getPortableObject($locale, $category, $text_domain);
        if (file_exists($filename)) {
            $output = [];
            $output['locale'] = $locale;
            $output['category'] = $category;
            $output['text_domain'] = $text_domain;
            $output['filename'] = basename($filename);
            $output['stat'] = $this->gettext->getStrings($locale, $category, $text_domain)->statistics()->toArray();
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
            $output['locale'] = $locale;
            $output['namespace'] = $namespace;
            $output['filename'] = basename($filename);
            $output['stat'] = $this->strings->getPhpStrings($locale, $namespace)->statistics()->toArray();
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
            $output['locale'] = $locale;
            $output['filename'] = basename($filename);
            $output['stat'] = $this->strings->getJsonStrings($locale)->statistics()->toArray();
        } else {
            $output = null;
        }

        return $output;
    }

}