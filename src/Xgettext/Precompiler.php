<?php

namespace Codewiser\Polyglot\Xgettext;

use Codewiser\Polyglot\Contracts\PrecompilerContract;
use Codewiser\Polyglot\FileSystem\FileHandler;
use Codewiser\Polyglot\Traits\FilesystemSetup;
use Codewiser\Polyglot\Xgettext\Precompilers\FallbackPrecompiler;
use Codewiser\Polyglot\Xgettext\Precompilers\VuePrecompiler;
use Codewiser\Polyglot\Xgettext\Precompilers\PhpPrecompiler;

class Precompiler implements PrecompilerContract
{
    use FilesystemSetup;

    protected function compiler(string $filename): PrecompilerContract
    {
        $filename = new FileHandler($filename);

        switch ($filename->extension()) {
            case 'php':
                $precompiler = new PhpPrecompiler();
                break;
            case 'vue':
                $precompiler = new VuePrecompiler();
                break;
            default:
                $precompiler = new FallbackPrecompiler();
                break;
        }

        $precompiler->setFilesystem($this->filesystem);
        $precompiler->setBasePath($this->base_path);
        $precompiler->setTempPath($this->temp_path);

        return $precompiler;
    }

    public function compiled(string $filename): array
    {
        return $this->compiler($filename)->compiled($filename);
    }
}
