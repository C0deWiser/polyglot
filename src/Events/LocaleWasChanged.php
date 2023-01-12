<?php

namespace Codewiser\Polyglot\Events;

class LocaleWasChanged
{
    public string $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }
}
