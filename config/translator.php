<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Polyglot mode
    |--------------------------------------------------------------------------
    |
    | This option is used to enable or disable gettext functionality.
    |
    | 'legacy'      - use pure Laravel Translator; collect string manually.
    | 'collector'   - use gettext for collecting string; use Laravel Translator for translating.
    | 'translator'  — use gettext for collecting string and for translating too.
    |
    */

    'mode' => 'inherit',

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
    'locales' => ['ru', 'en'],

    /*
    |--------------------------------------------------------------------------
    | Gettext Executables Configuration
    |--------------------------------------------------------------------------
    |
    | Provide paths to gettext shell scripts.
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
    | Inherit Strings Configuration
    |--------------------------------------------------------------------------
    */
    'legacy' => [
        'validation.',
        'passwords.',
        'auth.',
        'pagination.',
        'verify.'
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
            resource_path('views')
        ],
        'excludes' => [
            storage_path()
        ],
        'storage' => [
            resource_path('lang')
        ]
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
    'gettext' => [
        'storage' => resource_path('i18n'),
        'compile' => resource_path('i18n'),

        'domain' => 'default',
    ],
];