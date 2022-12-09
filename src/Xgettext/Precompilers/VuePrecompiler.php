<?php

namespace Codewiser\Polyglot\Xgettext\Precompilers;

use Codewiser\Polyglot\FileSystem\FileHandler;
use Illuminate\Support\Str;

class VuePrecompiler extends FallbackPrecompiler
{
    public function compiled(string $filename): array
    {
        // Extract script and template sections

        $filename = new FileHandler($filename);

        $content = $filename->getContent();

        $script = $this->makeTemporary($filename . '.js');
        $script->putContent(
            $this->script(
                Str::between($content, '<script', '</script>')
            )
        );

        $template = $this->makeTemporary($filename . '.php');
        $template->putContent(
            $this->template(
                Str::between($content, '<template>', '</template>')
            )
        );

        return [
            $script,
            $template
        ];
    }

    protected function script(string $content): string
    {
        $content = Str::after($content, '>');

        return $content;
    }

    protected function template(string $content): string
    {
        $content = Str::replace(['{{', '}}'], ['<?php ', ' ?>'], $content);

        $content = preg_replace(
            '/(:\S+=["\'])(.*?)(["\'][\s\n>])/i',
            '$1 <?php $2 ?> $3',
        $content
        );

        return $content;
    }
}
