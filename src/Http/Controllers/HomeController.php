<?php

namespace Codewiser\Polyglot\Http\Controllers;

use Codewiser\Polyglot\Http\Translations;
use Codewiser\Polyglot\Polyglot;
use Illuminate\Support\Facades\App;

class HomeController extends Controller
{
    use Translations;

    /**
     * Single page application catch-all route.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('polyglot::layout', [
            'assetsAreCurrent' => Polyglot::assetsAreCurrent(),
            'cssFile' => 'app.css',
            'polyglotScriptVariables' => Polyglot::scriptVariables(),
            'isDownForMaintenance' => App::isDownForMaintenance(),
            'trans' => $this->getTranslations(app()->getLocale())
        ]);
    }
}
