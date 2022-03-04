<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Polyglot Switch
    |--------------------------------------------------------------------------
    |
    | Disabled Polyglot provides Artisan console command to extract translation
    | strings and web panel to manage translations.
    |
    | Enabled Polyglot replaces Laravel Translator service, bringing Gettext
    | support to the Application. With full backward compatability.
    |
    */

    'enabled' => env('POLYGLOT_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Application Locales Configuration
    |--------------------------------------------------------------------------
    |
    | The application locales determines the listing of locales that will be used
    | by Polyglot to populate collected translation strings across locales.
    |
    | To avoid server specific issues use locale names applicable to
    | https://www.php.net/manual/ru/function.setlocale.php function.
    |
    |
    */

    'locales' => ['en_US'],

    /*
    |--------------------------------------------------------------------------
    | Polyglot Extractor Configuration
    |--------------------------------------------------------------------------
    |
    | Extractor parses source codes, finding translation strings.
    |
    | 'xgettext`    - extracts translation strings from source codes
    |                 using the power of xgettext utility.
    |
    */

    'extractor' => 'xgettext',

    /*
    |--------------------------------------------------------------------------
    | Xgettext Extractor Configuration
    |--------------------------------------------------------------------------
    |
    | Gettext groups translations into 'text domains', so we need to configure
    | at least one. For every text domain configure source files and folders
    | to parse and optionally exclude some files and folders from being parsed.
    |
    */

    'xgettext' => [
        [
            'sources' => [
                app_path(),
                resource_path('views')
            ],
            'exclude' => [],
        ]
    ],

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
    | Paths to gettext binaries.
    |
    */

    'executables' => [
        'xgettext' => env('XGETTEXT_EXECUTABLE', 'xgettext'),
        'msginit' => env('MSGINIT_EXECUTABLE', 'msginit'),
        'msgmerge' => env('MSGMERGE_EXECUTABLE', 'msgmerge'),
        'msgfmt' => env('MSGFMT_EXECUTABLE', 'msgfmt'),
        'msgcat' => env('MSGCAT_EXECUTABLE', 'msgcat'),
        'npm_xgettext' => env('NPM_EASYGETTEXT', 'gettext-extract')
    ],

    /*
    |--------------------------------------------------------------------------
    | Polyglot Logger
    |--------------------------------------------------------------------------
    |
    | False to disable, true or channel name to enable.
    |
    */

    'log' => env('POLYGLOT_LOG', false),
];
