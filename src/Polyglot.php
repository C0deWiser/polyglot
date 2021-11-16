<?php

namespace Codewiser\Polyglot;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Polyglot extends \Illuminate\Translation\Translator
{
    /**
     * Gettext domain.
     *
     * @var string
     */
    protected string $text_domain;

    protected string $loaded_domain = '';
    protected string $current_locale = '';

    /**
     * When application sets locale we will set up gettext.
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        parent::setLocale($locale);

        $this->putEnvironment($locale);
        $this->loadTranslations();
    }

    /**
     * Changing text domain will reconfigure gettext.
     *
     * @param string $text_domain
     */
    public function setTextDomain(string $text_domain)
    {
        $this->text_domain = $text_domain;
        $this->loadTranslations();
    }

    /**
     * Check if key suitable for php lang file.
     * Pattern is [namespace::]group.key[.dot.separated]
     *
     * @param string $key
     * @return bool
     */
    public static function isDotSeparatedKey(string $key): bool
    {
        return preg_match('~^(\w+::)?(\w+\.)+\w+$~i', $key);
    }

    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $value = parent::get($key, $replace, $locale, $fallback);

        if ($value != $key) {
            // Parent has translated the key.
            return $value;
        }

        if ($locale) {
            // Change
            $this->putEnvironment($locale);
        }

        $string = gettext($key);

        if ($locale) {
            // Restore
            $this->putEnvironment($this->getLocale());
        }

        return $this->makeReplacements($string, $replace);
    }

    public function choice($key, $number, array $replace = [], $locale = null)
    {
        $value = parent::choice($key, $number, $replace, $locale);

        if ($value != $key) {
            // Parent has translated the key.
            return $value;
        }

        if ($locale) {
            // Change
            $this->putEnvironment($locale);
        }

        $plurals = explode('|', $key);
        $msg_id = array_shift($plurals);
        $msg_id_plural = $plurals ? array_shift($plurals) : $msg_id;

        $string = ngettext($msg_id, $msg_id_plural, $number);

        if ($locale) {
            // Restore
            $this->putEnvironment($this->getLocale());
        }

        return $this->makeReplacements($string, $replace);
    }

    /**
     * Configure environment to gettext load proper files.
     *
     * @param string $locale
     */
    protected function putEnvironment(string $locale)
    {
        if ($this->current_locale != $locale) {
            $this->current_locale = $locale;

            putenv('LANG=' . $locale);
        }
    }

    /**
     * Load translated strings into PHP memory.
     *
     * @return void
     */
    protected function loadTranslations()
    {
        if ($this->loaded_domain != $this->text_domain) {

            textdomain($this->text_domain);
            bindtextdomain($this->text_domain, resource_path('lang'));
            bind_textdomain_codeset($this->text_domain, 'UTF-8');

            $this->loaded_domain = $this->text_domain;
        }
    }

    /**
     * Determine if Polyglot's published assets are up-to-date.
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    public static function assetsAreCurrent(): bool
    {
        $publishedPath = public_path('vendor/polyglot/mix-manifest.json');

        if (!File::exists($publishedPath)) {
            throw new \RuntimeException('Polyglot assets are not published. Please run: php artisan polyglot:publish');
        }

        return File::get($publishedPath) === File::get(__DIR__ . '/../public/mix-manifest.json');
    }

    /**
     * Get the default JavaScript variables for Polyglot.
     *
     * @return array
     */
    public static function scriptVariables(): array
    {
        return [
            'path' => config('polyglot.path'),
        ];
    }

    /**
     * Polyglot version.
     *
     * @return string
     */
    public static function version(): string
    {
        $composer = __DIR__ . '/../composer.json';
        $data = json_decode(file_get_contents($composer), true);
        return (string)$data['version'];
    }

    /**
     * Get registered extractors.
     *
     * @return ExtractorsManager
     */
    public static function manager(): ExtractorsManager
    {
        return app(ExtractorsManager::class);
    }

    /**
     * @return array
     */
    public static function getLocales(): array
    {
        return config('polyglot.locales');
    }

    public static function getCategoryName(int $category): string
    {
        switch ($category) {
            case LC_CTYPE:
                return 'LC_CTYPE';
            case LC_NUMERIC:
                return 'LC_NUMERIC';
            case LC_TIME:
                return 'LC_TIME';
            case LC_COLLATE:
                return 'LC_COLLATE';
            case LC_MONETARY:
                return 'LC_MONETARY';
            case LC_MESSAGES:
                return 'LC_MESSAGES';
            case LC_ALL:
                return 'LC_ALL';
            default:
                return 'UNKNOWN';
        }
    }
}