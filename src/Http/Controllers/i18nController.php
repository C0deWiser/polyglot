<?php

namespace Codewiser\Polyglot\Http\Controllers;

use Codewiser\Polyglot\Collections\FileCollection;
use Codewiser\Polyglot\Contracts\ExtractorContract;
use Codewiser\Polyglot\FileSystem\DirectoryHandler;
use Codewiser\Polyglot\Polyglot;
use Illuminate\Http\Request;

class i18nController extends Controller
{
    public function index()
    {
        $config = config('polyglot');

        $lang_path = new DirectoryHandler(lang_path());

        $data = [
            'enabled' => $config['enabled'],
            'locales' => Polyglot::getLocales(),
            'stat' => $lang_path->exists() ? $lang_path->allFiles()->statistics()->toArray() : []
        ];

        $lastCollected = Polyglot::extractors()->getExtracted()->lastModified();
        $lastTranslated = $lang_path->exists() ? $lang_path->allFiles()->translatable()->lastModified() : null;
        $lastCompiled = $lang_path->exists() ? $lang_path->allFiles()->mo()->lastModified() : null;

        $data['lastTranslated'] = $lastTranslated ? $lastTranslated->diffForHumans() : trans('Unknown');
        $data['lastCollected'] = $lastCollected ? $lastCollected->diffForHumans() : trans('Unknown');
        $data['lastCompiled'] = $lastCompiled ? $lastCompiled->diffForHumans() : trans('Unknown');

        return response()->json($data);
    }

}
