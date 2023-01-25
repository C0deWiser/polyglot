<?php

if (!function_exists('lang_path')) {

    /**
     * Get the path to the language folder.
     *
     * @param string $path
     * @return string
     */
    function lang_path(string $path = ''): string
    {
        $path = trim($path, DIRECTORY_SEPARATOR);
        $path = $path ? 'lang' . DIRECTORY_SEPARATOR . $path : 'lang';

        if (file_exists(base_path('lang'))) {
            // Laravel 9+
            return base_path($path);
        }

        // Laravel 8
        return resource_path($path);
    }
}

if (!function_exists('pgettext')) {
    /**
     * Particular (with context) gettext.
     *
     * @param string $context
     * @param string $message
     * @return string
     */
    function pgettext(string $context, string $message): string
    {
        $string = "{$context}\004{$message}";
        $translation = gettext($string);
        if ($translation == $string) return $message;
        else return $translation;
    }
}

if (!function_exists('dpgettext')) {
    /**
     * Particular (with context) dgettext.
     *
     * @param string $domain
     * @param string $context
     * @param string $message
     * @return string
     */
    function dpgettext(string $domain, string $context, string $message): string
    {
        $string = "{$context}\004{$message}";
        $translation = dgettext($domain, $string);
        if ($translation == $string) return $message;
        else return $translation;
    }
}

if (!function_exists('dcpgettext')) {
    /**
     * Particular (with context) dcgettext.
     *
     * @param string $domain
     * @param string $context
     * @param string $message
     * @param int $category
     * @return string
     */
    function dcpgettext(string $domain, string $context, string $message, int $category): string
    {
        $string = "{$context}\004{$message}";
        $translation = dcgettext($domain, $string, $category);
        if ($translation == $string) return $message;
        else return $translation;
    }
}

if (!function_exists('npgettext')) {
    /**
     * Particular (with context) ngettext.
     *
     * @param string $context
     * @param string $singular
     * @param string $plural
     * @param int $count
     * @return string
     */
    function npgettext(string $context, string $singular, string $plural, int $count): string
    {
        $string = "{$context}\004{$singular}";
        $translation = ngettext($string, $plural, $count);
        if ($translation == $string) return $singular;
        else return $translation;
    }
}

if (!function_exists('dnpgettext')) {
    /**
     * Particular (with context) dngettext.
     *
     * @param string $domain
     * @param string $context
     * @param string $singular
     * @param string $plural
     * @param int $count
     * @return string
     */
    function dnpgettext(string $domain, string $context, string $singular, string $plural, int $count): string
    {
        $string = "{$context}\004{$singular}";
        $translation = dngettext($domain, $string, $plural, $count);
        if ($translation == $string) return $singular;
        else return $translation;
    }
}

if (!function_exists('dcnpgettext')) {
    /**
     * Particular (with context) dcngettext.
     *
     * @param string $domain
     * @param string $context
     * @param string $singular
     * @param string $plural
     * @param int $count
     * @param int $category
     * @return string
     */
    function dcnpgettext(string $domain, string $context, string $singular, string $plural, int $count, int $category): string
    {
        $string = "{$context}\004{$singular}";
        $translation = dcngettext($domain, $string, $plural, $count, $category);
        if ($translation == $string) return $singular;
        else return $translation;
    }
}
