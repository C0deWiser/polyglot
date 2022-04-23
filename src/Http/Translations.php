<?php

namespace Codewiser\Polyglot\Http;

trait Translations
{
    private string $path = __DIR__ . '/../../resources/lang';

    protected function getLocales(): array
    {
        return array_map(function (string $path) {
            return basename($path, '.json');
        }, glob($this->path . '/*.json'));
    }

    protected function getTranslations(string $lang): array {
        if (in_array($lang, $this->getLocales())) {
            return json_decode(file_get_contents($this->path . '/' . $lang . '.json'), true);
        }

        return [];
    }
}
