<?php
if (!function_exists('pgettext')) {
    function pgettext($context, $msgid)
    {
        $contextString = "{$context}\004{$msgid}";
        $translation = gettext($contextString);
        if ($translation == $contextString) return $msgid;
        else return $translation;
    }
}