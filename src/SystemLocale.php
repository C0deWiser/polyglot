<?php

namespace Codewiser\Polyglot;

use Illuminate\Support\Facades\Process;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class SystemLocale
{
    protected ?string $current_system_locale = null;
    protected array $system_locales = [];

    public function __construct(
        protected array $system_preferences = [],
        protected ?LoggerInterface $logger = null,
        protected ?CacheInterface $cache = null,
    ) {
        //
    }

    public function remember(string $system_locale): void
    {
        $this->current_system_locale = $system_locale;
    }

    public function changed(string $system_locale): bool
    {
        return $this->current_system_locale !== $system_locale;
    }

    public function detect(string $locale): string
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

        $supported = $this->getSystemLocales($locale);

        foreach ($preferred as $preferredLocale) {
            if (in_array($preferredLocale, $supported)) {
                $systemLocale = $preferredLocale;
                break;
            }
        }

        if (!$systemLocale) {
            $this->logger?->error("No system locale found for $locale");
            $systemLocale = $preferred[0] ?? $supported[0] ?? $locale;
        }

        $this->logger?->debug("Use $systemLocale as system locale for $locale");

        $this->system_locales[$locale] = $systemLocale;

        return $systemLocale;
    }

    protected function getSystemLocales(string $locale): array
    {
        $supported = $this->cache?->get(__METHOD__.$locale);

        if (!$supported) {
            $command = "locale -a | grep $locale";
            $result = Process::run($command);

            if ($result->successful()) {
                $supported = array_filter(explode("\n", $result->output()));

                usort($supported, function ($a, $b) {
                    if (strlen($a) == strlen($b)) {
                        return 0;
                    }

                    if (str_ends_with($a, '.UTF-8') && !str_ends_with($b, '.UTF-8')) {
                        return -1;
                    }

                    if (!str_ends_with($a, '.UTF-8') && str_ends_with($b, '.UTF-8')) {
                        return 1;
                    }

                    return (strlen($a) < strlen($b)) ? -1 : 1;
                });

                $this->cache?->set(__METHOD__.$locale, $supported, now()->addDay());

                $this->logger?->debug($command, $supported);
            } else {
                $this->logger?->alert($command, [
                    'exitCode'    => $result->exitCode(),
                    'errorOutput' => $result->errorOutput(),
                ]);
            }
        }

        return is_array($supported) ? $supported : [];
    }
}