<?php

namespace Codewiser\Polyglot\Collections;

use Illuminate\Support\Collection;

class StringsCollection extends Collection
{
    public function untranslated(): StringsCollection
    {
        return $this
            ->reject(function ($string) {
                return $string;
            });
    }
}