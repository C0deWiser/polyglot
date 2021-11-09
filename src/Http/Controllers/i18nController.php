<?php

namespace Codewiser\Polyglot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class i18nController extends Controller
{
    public function index()
    {
        $config = config('polyglot');

        $data = [
            'mode' => $config['mode'],
            'locales' => $config['locales']
        ];

        $domains = [];

        if (isset($config['domains'])) {
            $domains = $config['domains'];
        } else {
            $domain = [
                'domain' => 'messages',
                'sources' => $config['sources'],
            ];
            if (isset($config['exclude']) && $config['exclude']) {
                $domain['exclude'] = $config['exclude'];
            }
            $domains[] = $domain;
        }

        $data['domains'] = $domains;
        $data['passthroughs'] = $config['passthroughs'];

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