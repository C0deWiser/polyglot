<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Polyglot mode
    |--------------------------------------------------------------------------
    |
    | This option is used to enable or disable some polyglot functionality.
    |
    | 'inherit'     - use pure Laravel Translator; collect string manually.
    | 'collector'   - use gettext for collecting string; use Laravel Translator for translating.
    | 'translator'  — use gettext for collecting string and for translating too.
    |
    */

    'mode' => env('POLYGLOT_MODE', 'inherit'),

    /*
    |--------------------------------------------------------------------------
    | Application Locales Configuration
    |--------------------------------------------------------------------------
    |
    | The application locales determines the listing of locales that will be used
    | by the translation service provider. This option is required to populate
    | translation strings across locales.
    |
    */
    'locales' => ['en'],

    /*
    |--------------------------------------------------------------------------
    | Polyglot Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Polyglot will be accessible from. If this
    | setting is null, Polyglot will reside under the same domain as the
    | application. Otherwise, this value will serve as the subdomain.
    |
    */

    'domain' => env('POLYGLOT_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Polyglot Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Polyglot will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => env('POLYGLOT_PATH', 'polyglot'),

    /*
    |--------------------------------------------------------------------------
    | Polyglot Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Polyglot route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => [
        'web'
    ],

    /*
    |--------------------------------------------------------------------------
    | Gettext Executables Configuration
    |--------------------------------------------------------------------------
    |
    | Paths to gettext shell scripts.
    |
    */
    'executables' => [
        'xgettext' => env('XGETTEXT_EXECUTABLE', 'xgettext'),
        'msginit' => env('MSGINIT_EXECUTABLE', 'msginit'),
        'msgmerge' => env('MSGMERGE_EXECUTABLE', 'msgmerge'),
        'msgfmt' => env('MSGFMT_EXECUTABLE', 'msgfmt'),
        'msgcat' => env('MSGCAT_EXECUTABLE', 'msgcat')
    ],

    /*
    |--------------------------------------------------------------------------
    | Gettext Collector Configuration
    |--------------------------------------------------------------------------
    |
    | Define resources to search translation strings in, exclude some resources
    | and store collected strings in configurable folder.
    |
    */
    'collector' => [
        'includes' => [
            app_path(),
            //resource_path('views')
        ],
        'excludes' => [
            storage_path()
        ],
        'storage' => resource_path('lang')
    ],

    /*
    |--------------------------------------------------------------------------
    | Gettext Translator Configuration
    |--------------------------------------------------------------------------
    |
    | Define folders to keep gettext files, set default gettext domain and list
    | translation strings that should be translated traditional way.
    |
    */
    'translator' => [
        'po' => resource_path('gettext'),
        'mo' => resource_path('gettext'),

        'domain' => 'messages',

        'passthroughs' => [
            'validation.',
            'passwords.',
            'auth.',
            'pagination.',
            'verify.'
        ],
    ],
];
