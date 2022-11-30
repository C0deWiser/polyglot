<?php

if (!function_exists('lang_folder')) {

    /**
     * Get the path to the language folder.
     *
     * @param string $path
     * @return string
     */
    function lang_folder(string $path = ''): string
    {
        if (function_exists('lang_path')) {
            return lang_path($path);
        }

        $path = ltrim($path, '/');

        return resource_path($path ? 'lang/' . $path : 'lang');
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
