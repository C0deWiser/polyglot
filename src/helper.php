<?php
if (!function_exists('pgettext')) {
    /**
     * Particular (with context) gettext.
     *
     * @param $msgctxt
     * @param $msgid
     * @return mixed|string
     */
    function pgettext($msgctxt, $msgid)
    {
        $string = "{$msgctxt}\004{$msgid}";
        $translation = gettext($string);
        if ($translation == $string) return $msgid;
        else return $translation;
    }
}

if (!function_exists('dpgettext')) {
    /**
     * Particular (with context) dgettext.
     *
     * @param $domain
     * @param $msgctxt
     * @param $msgid
     * @return mixed|string
     */
    function dpgettext($domain, $msgctxt, $msgid) {
        $string = "{$msgctxt}\004{$msgid}";
        $translation = dgettext($domain, $string);
        if ($translation == $string) return $msgid;
        else return $translation;
    }
}

if (!function_exists('dcpgettext')) {
    /**
     * Particular (with context) dcgettext.
     *
     * @param $domain
     * @param $msgctxt
     * @param $msgid
     * @param $category
     * @return mixed|string
     */
    function dcpgettext($domain, $msgctxt, $msgid, $category) {
        $string = "{$msgctxt}\004{$msgid}";
        $translation = dcgettext($domain, $string, $category);
        if ($translation == $string) return $msgid;
        else return $translation;
    }
}

if (!function_exists('npgettext')) {
    /**
     * Particular (with context) ngettext.
     *
     * @param $msgctxt
     * @param $msgid
     * @param $msgid_plural
     * @param $count
     * @return mixed|string
     */
    function npgettext($msgctxt, $msgid, $msgid_plural, $count)
    {
        $string = "{$msgctxt}\004{$msgid}";
        $translation = ngettext($string, $msgid_plural, $count);
        if ($translation == $string) return $msgid;
        else return $translation;
    }
}

if (!function_exists('dnpgettext')) {
    /**
     * Particular (with context) dngettext.
     *
     * @param $domain
     * @param $msgctxt
     * @param $msgid
     * @param $msgid_plural
     * @param $count
     * @return mixed|string
     */
    function dnpgettext($domain, $msgctxt, $msgid, $msgid_plural, $count) {
        $string = "{$msgctxt}\004{$msgid}";
        $translation = dngettext($domain, $string, $msgid_plural, $count);
        if ($translation == $string) return $msgid;
        else return $translation;
    }
}

if (!function_exists('dcnpgettext')) {
    /**
     * Particular (with context) dcngettext.
     *
     * @param $domain
     * @param $msgctxt
     * @param $msgid
     * @param $count
     * @param $category
     * @return mixed|string
     */
    function dcnpgettext($domain, $msgctxt, $msgid, $msgid_plural, $count, $category) {
        $string = "{$msgctxt}\004{$msgid}";
        $translation = dcngettext($domain, $string, $msgid_plural, $count, $category);
        if ($translation == $string) return $msgid;
        else return $translation;
    }
}