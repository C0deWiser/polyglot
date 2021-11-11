<?php

namespace Codewiser\Polyglot\Collections;

use Codewiser\Polyglot\Contracts\StringsCollectionInterface;
use Codewiser\Polyglot\Contracts\StringsStatisticsContract;
use Illuminate\Support\Collection;

class StringsCollection extends Collection implements StringsCollectionInterface
{
    public function untranslated(): StringsCollection
    {
        return $this
            ->reject(function ($string) {
                return $string['value'];
            });
    }

    public function translated(): StringsCollectionInterface
    {
        return $this
            ->filter(function ($string) {
                return $string['value'];
            });
    }

    public function fuzzy(): StringsCollectionInterface
    {
        return $this->reject();
    }

    public function statistics(): StringsStatisticsContract
    {
        return new StringsStatistics($this);
    }

    public function api(): StringsCollectionInterface
    {
        return $this
            ->map(function ($item) {
                return [
                    'msgid' => $item['key'],
                    'msgstr' => $item['value']
                ];
            });
    }
}