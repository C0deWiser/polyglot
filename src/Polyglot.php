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
    protected string $domain;

    /**
     * Folder with gettext mo files.
     *
     * @var string
     */
    protected string $compiled;
    protected string $loaded_domain = '';
    protected string $current_locale = '';

    /**
     * Translate this strings using Translator service.
     *
     * @var array
     */
    protected array $passthroughs;

    public function __construct(Loader $loader, $locale, string $domain, string $compiled, array $passthroughs)
    {
        $this->domain = $domain;
        $this->compiled = $compiled;
        $this->passthroughs = $passthroughs;

        parent::__construct($loader, $locale);
    }

    public function setLocale($locale)
    {
        parent::setLocale($locale);

        $this->putEnvironment($locale);
        $this->loadTranslations();
    }

    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        if ($this->shouldPassThrough($key)) {
            return parent::get($key, $replace, $locale, $fallback);
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
        if ($this->shouldPassThrough($key)) {
            return parent::choice($key, $number, $replace, $locale);
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

    protected function shouldPassThrough(string $key): bool
    {
        return Str::startsWith($key, $this->passthroughs);
    }

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
        if ($this->loaded_domain != $this->domain) {

            textdomain($this->domain);
            bindtextdomain($this->domain, $this->compiled);
            bind_textdomain_codeset($this->domain, 'UTF-8');

            $this->loaded_domain = $this->domain;
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

    public static function collector(): StringsCollector
    {
        return app(StringsCollector::class);
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

}