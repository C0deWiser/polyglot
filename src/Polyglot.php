<?php

namespace Codewiser\Polyglot;

use Countable;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class Polyglot extends \Illuminate\Translation\Translator
{
    protected static array $supported_locales;

    public function __construct(
        Loader $loader,
        $locale,
        protected string $text_domain,
        protected string $codeset,
        array $supported_locales,
        protected ?LoggerInterface $logger = null,
    ) {
        self::$supported_locales = $supported_locales;

        parent::__construct($loader, $locale);
    }

    /**
     * When application sets locale we will set up gettext.
     *
     * @param  string  $locale
     */
    public function setLocale($locale): void
    {
        parent::setLocale($locale);

        $this->putEnvironment($locale);

        $this->loadTranslations();
    }

    /**
     * Changing text domain will reconfigure gettext.
     *
     * @param  string  $text_domain
     */
    public function setTextDomain(string $text_domain): void
    {
        $this->text_domain = $text_domain;
        $this->loadTranslations();
    }

    /**
     * Check if key suitable for php lang file.
     * Pattern is [namespace::]group.key[.dot.separated]
     *
     * @param  string  $key
     *
     * @return bool
     */
    public static function isDotSeparatedKey(string $key): bool
    {
        return preg_match('~^(\w+::)?(\w+\.)+\w+$~i', $key);
    }

    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        if (parent::get($key, [], $locale, $fallback) != $key) {
            // Parent successfully translate given key
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
        // Replacing built-in placeholder
        $_key = Str::replace(':count', 'count', $key);
        $keys = explode('|', $_key);
        if (!in_array(parent::choice($_key, $number, [], $locale), $keys)) {
            // Parent successfully translate given key
            return parent::choice($key, $number, $replace, $locale);
        }

        if ($locale) {
            // Change
            $this->putEnvironment($locale);
        }

        $plurals = explode('|', $key);
        $msg_id = array_shift($plurals);
        $msg_id_plural = $plurals ? array_shift($plurals) : $msg_id;

        if (is_array($number) || $number instanceof Countable) {
            $number = count($number);
        }

        $string = ngettext($msg_id, $msg_id_plural, $number);

        if ($locale) {
            // Restore
            $this->putEnvironment($this->getLocale());
        }

        // Built-in placeholder
        $replace['count'] = $number;

        return $this->makeReplacements($string, $replace);
    }

    /**
     * Configure environment to gettext load proper files.
     *
     * @param  string  $locale
     */
    protected function putEnvironment(string $locale): void
    {
        $result = putenv("LANG=$locale");
        $this->logger?->debug("putenv(LANG=$locale) == $result");

        $result = putenv("LC_ALL=$locale");
        $this->logger?->debug("putenv(LC_ALL=$locale) == $result");

        $locales = self::$supported_locales[$locale] ?? [$locale];

        $result = setLocale(LC_ALL, $locales);
        $this->logger?->debug("setLocale(LC_ALL, ".json_encode($locales).") == $result");
    }

    /**
     * Load translated strings into PHP memory.
     *
     * @return void
     */
    protected function loadTranslations(): void
    {
        $result = textdomain($this->text_domain);
        $this->logger?->debug("textdomain($this->text_domain) == $result");

        $result = bindtextdomain($this->text_domain, lang_path());
        $this->logger?->debug("bindtextdomain($this->text_domain, ".lang_path().") == $result");

        $result = bind_textdomain_codeset($this->text_domain, $this->codeset);
        $this->logger?->debug("bind_textdomain_codeset($this->text_domain, $this->codeset) == $result");
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

        return File::get($publishedPath) === File::get(__DIR__.'/../public/mix-manifest.json');
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
        $composer = __DIR__.'/../composer.json';
        $data = json_decode(file_get_contents($composer), true);
        return (string) ($data['version'] ?? 'unknown');
    }

    /**
     * Get registered extractors.
     *
     * @return ExtractorsManager
     */
    public static function extractors(): ExtractorsManager
    {
        return app(ExtractorsManager::class);
    }

    /**
     * Get registered compilers.
     *
     * @return CompilerManager
     */
    public static function compilers(): CompilerManager
    {
        return app(CompilerManager::class);
    }

    /**
     * Get array of known locales.
     *
     * @return array
     */
    public static function getLocales(): array
    {
        return array_keys(config('polyglot.locales'));
    }

    public static function getCategoryName(int $category): string
    {
        return match ($category) {
            LC_CTYPE    => 'LC_CTYPE',
            LC_NUMERIC  => 'LC_NUMERIC',
            LC_TIME     => 'LC_TIME',
            LC_COLLATE  => 'LC_COLLATE',
            LC_MONETARY => 'LC_MONETARY',
            LC_MESSAGES => 'LC_MESSAGES',
            LC_ALL      => 'LC_ALL',
            default     => 'UNKNOWN',
        };
    }
}
