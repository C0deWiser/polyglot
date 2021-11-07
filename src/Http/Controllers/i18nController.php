<?php

namespace Codewiser\Polyglot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class i18nController extends Controller
{
    public function index()
    {
        $data = [
            'mode' => config('polyglot.mode'),
            'locales' => config('polyglot.locales')
        ];

        $data['translator'] = config('polyglot.translator');
        $data['collector'] = config('polyglot.collector');

        return response()->json($data);
    }

    public function collect()
    {
        Artisan::call('polyglot:collect');
    }

    public function compile()
    {
        Artisan::call('polyglot:compile');
    }
}