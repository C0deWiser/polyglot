<?php

namespace Codewiser\Polyglot;

use Codewiser\Polyglot\Contracts\ManipulatorInterface;
use Codewiser\Polyglot\Manipulators\GettextManipulator;
use Codewiser\Polyglot\Manipulators\StringsManipulator;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Sepia\PoParser\Catalog\Entry;

class Polyglot extends \Illuminate\Translation\Translator
{
    /**
     * Gettext domain.
     *
     * @var string
     */
    protected string $text_domain;

    /**
     * List of supported locales;
     *
     * @var array
     */
    protected array $locales;

    protected string $loaded_domain = '';
    protected string $current_locale = '';

    /**
     * Translate this strings using Translator service.
     *
     * @var array
     */
    protected array $passthroughs;

    public function __construct(Loader $loader, $locale, string $text_domain, array $passthroughs)
    {
        $this->text_domain = $text_domain;
        $this->passthroughs = $passthroughs;

        parent::__construct($loader, $locale);
    }

    public function setLocale($locale)
    {
        parent::setLocale($locale);

        $this->putEnvironment($locale);
        $this->loadTranslations();
    }

    public function setTextDomain(string $text_domain)
    {
        $this->text_domain = $text_domain;
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
        if ($this->loaded_domain != $this->text_domain) {

            textdomain($this->text_domain);
            bindtextdomain($this->text_domain, $this->loader->storage());
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
     * @return ExtractorsManager|null
     */
    public static function manager(): ?ExtractorsManager
    {
        return app(ExtractorsManager::class);
    }

    /**
     * Get proper manipulator.
     *
     * @return ManipulatorInterface|null
     */
    public static function manipulator(): ?ManipulatorInterface
    {
        return app(ManipulatorInterface::class);
    }

    public function all($locale, $text_domain = null, $category = 'LC_MESSAGES'): Collection
    {
        $manipulator = self::manipulator();

        if ($text_domain && $manipulator instanceof GettextManipulator) {
            return $manipulator->getStrings($locale, $category, $text_domain);
        }

        if ($manipulator instanceof StringsManipulator) {
            $strings = $manipulator->getJsonStrings($locale);

            if ($text_domain) {
                $strings->merge(
                    $manipulator->getPhpStrings($locale, $text_domain)
                        ->mapWithKeys(function ($value, $key) use ($text_domain) {
                            return [$text_domain . '.' . $key => $value];
                        })
                );
            } else {
                foreach ($manipulator->getPhpListing($locale) as $filename) {
                    $text_domain = basename($filename, '.php');
                    $strings->merge(
                        $manipulator->getPhpStrings($locale, $text_domain)
                            ->mapWithKeys(function ($value, $key) use ($text_domain) {
                                return [$text_domain . '.' . $key => $value];
                            })
                    );
                }
            }

            return $strings;
        }

        return new Collection();
    }

    /**
     * @return array
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * @param array $locales
     */
    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }
}