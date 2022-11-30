<?php

namespace Codewiser\Polyglot\Xgettext\Precompilers;

use Codewiser\Polyglot\Contracts\PrecompilerContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileContract;
use Codewiser\Polyglot\FileSystem\FileHandler;
use Codewiser\Polyglot\Traits\FilesystemSetup;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;

class PhpPrecompiler extends FallbackPrecompiler
{
    public function compiled(string $filename): array
    {
        $filename = new FileHandler($filename);

        if (Str::endsWith($filename, '.blade.php')) {
            $tmp = $this->makeTemporaryBlade($filename);
        } else {
            $tmp = $this->makeTemporaryCopy($filename);
        }
        $this->prepareTemporary($tmp);

        return [
            $tmp
        ];
    }

    /**
     * Compile given blade template into temporary php file.
     *
     * @param FileContract $filename
     * @return FileContract
     */
    protected function makeTemporaryBlade(FileContract $filename): FileContract
    {
        $tmp = $this->temporize($filename);
        $tmp->delete();
        $tmp->parent()->ensureDirectoryExists();

        $compiler = new BladeCompiler($this->filesystem, $tmp->parent());

        try {
            $tmp->putContent($compiler->compileString($filename->getContent()));
        } catch (FileNotFoundException $e) {
        }

        return $tmp;
    }

    /**
     * Prepare given temporary file to be parsed by xgettext.
     *
     * @param FileContract $filename
     */
    protected function prepareTemporary(FileContract $filename)
    {
        try {
            $content = $filename->getContent();
        } catch (FileNotFoundException $e) {
            return;
        }

        $content = Str::replace("app('translator')->get", '__', $content);
        $content = Str::replace("Lang::get", ' __', $content);
        $content = preg_replace(
            '~trans_choice\s*?\(\s*?[\'"](.*?)\|(.*?)[\'"]\s*?,(.+?)\)~mi',
            "ngettext('$1', '$2', $3)",
            $content
        );
        $content = preg_replace(
            '~trans_choice\s*?\(\s*?[\'"](.*?)[\'"]\s*?,(.+?)\)~mi',
            "ngettext('$1', '$1', $2)",
            $content
        );

        $filename->putContent($content);
    }
}
