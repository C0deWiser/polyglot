# Laravel Translator

Translate Laravel applications using modern ui.

## Concepts

Visually it would be like Horizon and Telescope. It will show all translated and untranslated strings for every locale, registered in application.

It allows translating and creating new strings.

It may populate strings through locales. Saying, if `en` has 100 strings, and `ru` only 10, it may create missing russian strings.

This translation service is to extend `\Illuminate\Translation\Translator`, adding Gettext support.

Gettext can perfectly extract strings from source codes, and it is powerfull translation service itself. Furthermore, we may combine both ways: translate some strings in a traditional way, and the rest using gettext.

We may utilize online translation services.

## Gettext

### As string collector

In that mode we will use `xgettext` to collect strings from application source codes. But `xgettext` can not detect Laravel's `trans_choice`. Can. Not. 

Collected strings will be stored to `lang` files  — `json` or `php` — depending on string signature.

### As translation service

In that mode we will store collected strings not in `lang` files, but in `po` files. After localizing, all `po` files will be compiled into `mo`.

This translation service will search strings both in `lang` (compatibility mode) and `mo` files.

This mode brings to application true support of pluralization.

### Settings

Service should know the list of application locales. Also, we should provide array of folders/files with source code to search strings in. We suppose there should be possibility to exclude some resources from scanning.

We may define strings that should be translated traditional way, e.g. `['validation.', 'passwords.']`.

## Dashboard

We need all the functionality of PoEdit. It is not an ideal, but good starting point.
