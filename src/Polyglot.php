<?php

namespace Codewiser\Polyglot;

use Countable;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class Polyglot extends \Illuminate\Translation\Translator
{
    protected string $loaded_domain = '';
    protected string $current_system_locale = '';
    protected array $system_locales = [];
    protected array $system_preferences = [];
    protected ?LoggerInterface $logger = null;

    public function __construct(Loader $loader, $locale, protected string $text_domain)
    {
        parent::__construct($loader, $locale);
    }

    public function setSystemPreferences(array $preferred_locales): static
    {
        $this->system_preferences = $preferred_locales;

        return $this;
    }

    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;

        return $this;
    }

    protected function systemLocale($locale): string
    {
        if (isset($this->system_locales[$locale])) {
            return $this->system_locales[$locale];
        }

        // Trying to determine gettext locale
        $systemLocale = null;
        $preferred = $this->system_preferences[$locale] ?? [];

        if (!$preferred) {
            $this->logger?->warning("No system preferences defined for locale $locale");
        }

        $command = "locale -a | grep $locale";
        $result = Process::run($command);

        if ($result->successful()) {
            $supported = array_filter(explode("\n", $result->output()));

            usort($supported, function ($a, $b) {
                if (strlen($a) == strlen($b)) {
                    return 0;
                }
                return (strlen($a) < strlen($b)) ? -1 : 1;
            });

            $this->logger?->debug($command, $supported);

            foreach ($preferred as $preferredLocale) {
                if (in_array($preferredLocale, $supported)) {
                    $systemLocale = $preferredLocale;
                    break;
                }
            }
            if (!$systemLocale) {
                $this->logger?->error("No system locale found for $locale");
            }
        } else {
            $this->logger?->alert($command, [
                'exitCode'    => $result->exitCode(),
                'errorOutput' => $result->errorOutput(),
            ]);
        }

        if (!$systemLocale) {
            $systemLocale = $preferred[0] ?? $supported[0] ?? $locale;
        }

        $this->logger?->debug("Use $systemLocale as system locale for $locale");

        $this->system_locales[$locale] = $systemLocale;

        return $systemLocale;
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
     * @param  string  $systemLocale
     */
    protected function putEnvironment(string $systemLocale): void
    {
        $systemLocale = $this->systemLocale($systemLocale);

        if ($this->current_system_locale != $systemLocale) {
            $this->current_system_locale = $systemLocale;

            $result = putenv('LANG='.$systemLocale);
            $this->logger?->debug("putenv(LANG=$systemLocale) == $result");

            $result = setLocale(LC_ALL, $systemLocale);
            $this->logger?->debug("setLocale(LC_ALL, $systemLocale) == $result");
        }
    }

    /**
     * Load translated strings into PHP memory.
     *
     * @return void
     */
    protected function loadTranslations(): void
    {
        if ($this->loaded_domain != $this->text_domain) {
            $result = textdomain($this->text_domain);
            $this->logger?->debug("textdomain($this->text_domain) == $result");

            $result = bindtextdomain($this->text_domain, lang_path());
            $this->logger?->debug("bindtextdomain($this->text_domain, ".lang_path().") == $result");

            $result = bind_textdomain_codeset($this->text_domain, 'UTF-8');
            $this->logger?->debug("bind_textdomain_codeset($this->text_domain, UTF-8) == $result");

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
     * Get known locales assoc array. Key is lang folder, value is ISO locale.
     *
     * @return array
     */
    public static function getLocales(): array
    {
        return config('polyglot.locales');
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
