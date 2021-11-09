# Polyglot

## Introduction

Polyglot provides a beautiful translation editor and can extract translations strings from the application source codes.    

Using Polyglot you may be sure, that you application is fully localized.

## Installation

Install [Gettext](https://www.gnu.org/software/gettext/) on your server and make sure, that php has `ext-gettext` extension enabled.

You may install Polyglot into your project using the Composer package manager:

    composer require codewiser/polyglot

After installing Polyglot, publish its assets using the polyglot:install Artisan command:

    php artisan polyglot:install

## Configuration

After publishing Polyglot's assets, its primary configuration file will be located at config/polyglot.php. This configuration file allows you to configure Polyglot working mode.

```php
'mode' => env('POLYGLOT_MODE', 'editor'),
```

Polyglot supports three working modes:

* `editor`
  
    Polyglot works only as translation online editor.

* `collector`

    Polyglot may find and extract translation strings from the application source codes.

    Collected strings will be placed into `resource/lang` directory, modifying existing files. 

* `translator`

  Polyglot replaces Laravel Translation Service, bringing all Gettext power to your application. With full backward compatability, however.

Polyglot scans files and folders, that are configured in `sources` property. It may be as a folder, as a single file or array if any filesystem resources.

```php
'sources' => [
    app_path(),
    resource_path('views')
],
```

Meanwhile, you may exclude some resources from being scanned.

```php
'exclude' => resource_path('views/auth'),
```

After collecting strings, Polyglot will populate collected strings through every configured locale.

```php
'locales' => ['en_US', 'en_GB', 'it', 'es'],
```

## Dashboard Authorization

Polyglot exposes a dashboard at the /polyglot URI. By default, you will only be able to access this dashboard in the local environment. However, within your `app/Providers/PolyglotServiceProvider.php` file, there is an authorization gate definition. This authorization gate controls access to Polyglot in non-local environments. You are free to modify this gate as needed to restrict access to your Horizon installation.

## Web editor

@todo

## Collecting strings

Once you have configured mode to `collector` in your application's `config/polyglot.php` configuration file, you may collect strings using the polyglot Artisan command. This single command will collect all translation strings from the configured sources:

    php artisan polyglot:collect
  
Polyglot uses `xgettext` to collect translation strings, understanding even `trans`, `trans_choice`, `@trans` and other Laravel specific directives.

After collecting strings is finished, your application's `resourse/lang` folder may look like:

```
resources/
  lang/
    es/
      auth.php
      passwords.php
    en_GB/
      auth.php
      passwords.php
    en_US/
      auth.php
      passwords.php
    it/
      auth.php
      passwords.php
    es.json
    en_GB.json
    en_US.json
    it.json
```

You only left to translate files.

## Gettext translator

As Laravel Translator may hold strings in different files, as Gettext may hold strings in different files. The idea is alike, but there are a lot of difference.

Also, Gettext may split strings by categories, described by php constants `LC_MESSAGES`, `LC_MONETARY`, `LC_TIME` and so on.

By default, Polyglot stores collected strings on `messages` domain in `LC_MESSAGES`category.

So, if you have configured Polyglot mode to `translator`, after you run `polyglot:collect` Artisan command, your application's `resourse/lang` folder may look like:

```
resources/
  lang/
    es/
      LC_MESSAGES/
        messages.po
    en_GB/
      LC_MESSAGES/
        messages.po
    en_US/
      LC_MESSAGES/
        messages.po
    it/
      LC_MESSAGES/
        messages.po
```

After you have translated `po` files you should compile it all to `mo` format, that is understandable by Gettext. Use Artisan command to compile.

    php artisan polyglot:compile

Beside every `po` file will appear `mo` file.

Do remember, that php caches contents of `mo` files. So, after compiling, be sure, you have restarted the web server.

### Compatability with Laravel Translator

Even using Gettext driver, you may continue to use Laravel translator directives, such as `trans` and `trans_choice`.

Meanwhile, you may use Gettext directives, such as `gettext`, `ngettext` and others.

Sometimes, you may want to keep existing translations as it is, and use `po` for only new strings. You may configure the array of translation keys, that should be placed into `php` files, not in `po`:

```php
'passthroughs' => [
    'validation.',
    'passwords.',
    'auth.',
    'pagination.',
    'verify.'
],
```

In that case, all strings, that begins with configured values, will be kept in `php` files, following Laravel Translator style.

### Multiple domains

Sometimes, you may want to divide your application's translation strings into few domains, e.g. strings for frontend and strings for administrative panel.

You may configure it that way:

```php
'domains' => [
  [
    'domain' => 'frontend', 
    'sources' => [
        app_path(),
        resource_path('views')
    ],
    'exclude' => resource_path('views/admin')
  ],
  [
    'domain' => 'admin', 
    'sources' => [
        resource_path('views/admin')
        resource_path('js/admin')
    ]
  ],
],
```

After collecting strings, every locale in `resource/lang` will get two files: `frontend.po` and `admin.po`.

By default, Polyglot will load into php memory the first configured domain. You may load next domain by accessing Laravel's `Lang` facade:

```php
Lang::setDomain('admin');
```

### Gettext and JavaScript

@todo   
experimental

Gettext do may collect strings from JavaScript (and from Vue, but there are some problems...). It is enough, if developer will define proper functions: `gettext`, `ngettext` etc.

Then loading you JavaScript app, just deliver the array of strings from back to front of your app.

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

public function translations(Request $request)
{
    return Lang::all(
        $request->get('locale'),
        $request->get('domain'),
    );
}
```

## Gettext

Polyglot supports the following Gettext directives.

* `gettext(string $message)`

  Lookup a message in the current domain.
* `ngettext(string $singular, string $plural, int $count)`

  Plural version of gettext.
* `pgettext(string $context, string $message)`

  Particular version of gettext.
* `npgettext(string $context, string $singular, string $plural, int $count)`

  Particular version of ngettext.

Other directives, that allows to override current domain and category, are not supported.

### The power of Gettext

Gettext can be very helpful for the translator. First, Gettext extracts references of the string.

```
#: /sources/php/second.php:3 /sources/js/first.js:1
msgid "Short message"
msgstr ""
```

Next, Gettext may extract developer comment.

```php
/* The message will be shown at test page only. */
echo gettext('Hello world');
```

```
#. The message will be shown at test page only.
msgid "Hello world"
msgstr ""
```

Finally, the developer may explicitly define the message context.

```php
gettext('May');
pgettext('Month', 'May');
```

```
msgid "May"
msgstr ""

msgctxt "Month"
msgid "May"
msgstr ""
```

All this practices is very helpful for achieving high quality localizations. 