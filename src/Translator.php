<?php

namespace Codewiser\Translation;

use Illuminate\Contracts\Translation\Loader;

class Translator extends \Illuminate\Translation\Translator
{
    /**
     * Gettext domain.
     *
     * @var string
     */
    protected $domain;

    /**
     * Gettext compiled files.
     *
     * @var string
     */
    protected $compiled;
    protected $loaded_domain;
    protected $current_locale;

    public function setLocale($locale)
    {
        parent::setLocale($locale);

        $this->putEnvironment($locale);
        $this->loadTranslations();
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;

        $this->loadTranslations();
    }

    protected function putEnvironment($locale)
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
        // Each time we switch locale, we should reset php mo-cache.
        // The only way to do that is to use different domains for each locale.

        if ($domain = $this->domain) {

            if ($this->loaded_domain != $domain) {

                textdomain($domain);
                bindtextdomain($domain, $this->compiled);
                bind_textdomain_codeset($domain, 'UTF-8');

                $this->loaded_domain = $domain;
            }
        }
    }

    /**
     * @param string $compiled
     */
    public function setCompiled(string $compiled)
    {
        $this->compiled = $compiled;
    }

}