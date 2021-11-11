<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Polyglot mode
    |--------------------------------------------------------------------------
    |
    | This option is used to enable or disable some Polyglot functionality.
    | Every next mode extends functionality of previous mode.
    |
    | 'editor'      - Polyglot provides online translation editor.
    |
    | 'collector'   - Polyglot may collect strings from source codes.
    |
    | 'translator'  — Polyglot replaces Translator service,
    |                 bringing Gettext support to the Application.
    |
    */

    'mode' => env('POLYGLOT_MODE', 'collector'),

    /*
    |--------------------------------------------------------------------------
    | Application Locales Configuration
    |--------------------------------------------------------------------------
    |
    | The application locales determines the listing of locales that will be used
    | by Polyglot to populate collected translation strings across locales.
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
    | Gettext Collector Configuration
    |--------------------------------------------------------------------------
    |
    | Define file system resources to search translation strings in,
    | excluding some. Collector will scan configured resources
    | and store strings into resource/lang folder.
    |
    | Working as a `collector` Polyglot will store collected strings
    | into .json and .php files. As `translator` Polyglot will store
    | collected strings into .po files (applicable to gettext).
    |
    */

    'sources' => [
        app_path(),
        resource_path('views')
    ],
    'exclude' => [],

    /*
    |--------------------------------------------------------------------------
    | Passthroughs Configuration
    |--------------------------------------------------------------------------
    |
    | When collecting strings as `translator` Polyglot may pass
    | some collected strings into .php files, storing others to .po files.
    |
    | It is rational to leave standard Laravel translations as it is.
    |
    */
    'passthroughs' => [
        'validation.',
        'passwords.',
        'auth.',
        'pagination.',
        'verify.'
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
        'msgcat' => env('MSGCAT_EXECUTABLE', 'msgcat')
    ],
];
