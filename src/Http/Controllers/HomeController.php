<?php

namespace Codewiser\Polyglot\Http\Controllers;

use Codewiser\Polyglot\Polyglot;
use Illuminate\Support\Facades\App;

class HomeController extends Controller
{
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
            'horizonScriptVariables' => Polyglot::scriptVariables(),
            'isDownForMaintenance' => App::isDownForMaintenance(),
        ]);
    }
}
