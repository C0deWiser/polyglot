<?php

namespace Codewiser\Polyglot\Http\Controllers;

use Codewiser\Polyglot\FileSystem\Contracts\DirectoryContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\Contracts\FinderContract;
use Codewiser\Polyglot\FileSystem\Contracts\ResourceContract;
use Codewiser\Polyglot\FileSystem\JsonFileHandler;
use Codewiser\Polyglot\FileSystem\PhpFileHandler;
use Codewiser\Polyglot\FileSystem\PoFileHandler;
use Codewiser\Polyglot\Polyglot;
use Illuminate\Http\Request;

class L10nController extends Controller
{

    public function get(FinderContract $finder, string $path = '/')
    {
        // Server, as usual, prevents calling random php
        // Unescape file extension
        $path = str_replace('__dot__', '.', $path);

        $finder->setPath($path);
        $resource = $finder->getPath();

        $response = [
            'path' => ($resource->exists() ? $resource->toArray() : []) + ['root' => $finder->isRoot()]
        ];

        if ($resource instanceof DirectoryContract) {
            $response['files'] = $finder->files()
                ->filter(function (ResourceContract $resource) {
                    if ($resource instanceof FileContract) {
                        return in_array($resource->extension(), ['json', 'php', 'po']);
                    }
                    return true;
                })
                ->map(function (ResourceContract $resource) {
                    $data = $resource->toArray();

                    if ($resource instanceof FileHandlerContract) {
                        $data['stat'] = $resource->statistics()->toArray();
                    }
                    if ($resource instanceof DirectoryContract) {
                        $data['stat'] = $resource->allFiles()->statistics()->toArray();
                    }

                    return $data;
                })
                ->values();
        }
        if ($resource instanceof FileHandlerContract) {
            $entries = $resource->allEntries();

            $response['strings'] = $entries->api();
            $response['stat'] = $entries->statistics()->toArray();

            if ($resource instanceof PoFileHandler) {
                $response['headers'] = $resource->headers();
            }
        }

        return response()
            ->json($response);
    }

    public function post(Request $request, FinderContract $finder, string $path)
    {
        $finder->setPath($path);

        $resource = $finder->getPath();

        if ($resource instanceof JsonFileHandler ||
            $resource instanceof PhpFileHandler) {
            $this->saveString($request, $resource);
        } elseif ($resource instanceof PoFileHandler) {
            $this->saveEntry($request, $resource);
        } else {
            abort(400);
        }
    }

    protected function saveString(Request $request, FileHandlerContract $resource)
    {
        $validated = $request->validate([
            'msgid' => 'required|string',
            'msgstr' => 'present'
        ]);

        $resource->putEntry(
            $validated['msgid'],
            $validated['msgstr']
        );
    }

    protected function saveEntry(Request $request, PoFileHandler $resource)
    {
        $rules = [
            'msgid' => 'required|string',
            'msgstr' => 'present',
            'fuzzy' => 'boolean',
            'comment' => 'nullable|string',
            'context' => 'nullable|string'
        ];

        $validated = $request->validate($rules);

        $entry = $resource->getEntry($validated);

        if (($entry && $entry->isPlural()) || (!$entry && $request->has('msgid_plural'))) {
            $rules['msgid_plural'] = 'required|string';
            $rules['msgstr'] = 'array|size:' . $resource->header()->getPluralFormsCount();
        }

        $validated = $request->validate($rules);

        $resource->putEntry($validated, $validated);

        $this->updateHeader($request, $resource);
    }

    protected function updateHeader(Request $request, PoFileHandler $resource)
    {
        $headers = [
            'X-Generator' => 'Polyglot ' . Polyglot::version(),
            'PO-Revision-Date' => now()->format('Y-m-d H:i:sO')
        ];

        if ($user = $request->user()) {
            $headers['Last-Translator'] = "{@$user->name} <{@$user->email}>";
        }

        $resource->updateHeader($headers);
    }

}
