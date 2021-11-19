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

        $lang_path = new DirectoryHandler(resource_path('lang'));

        $data = [
            'enabled' => $config['enabled'],
            'locales' => array_values($config['locales']),
            'stat' => $lang_path->allFiles()->statistics()->toArray()
        ];

        $lastCollected = Polyglot::extractors()->getExtracted()->lastModified();
        $lastTranslated = $lang_path->allFiles()->translatable()->lastModified();
        $lastCompiled = $lang_path->allFiles()->mo()->lastModified();

        $data['lastTranslated'] = $lastTranslated ? $lastTranslated->diffForHumans() : 'Unknown';
        $data['lastCollected'] = $lastCollected ? $lastCollected->diffForHumans() : 'Unknown';
        $data['lastCompiled'] = $lastCompiled ? $lastCompiled->diffForHumans() : 'Unknown';

        return response()->json($data);
    }

}