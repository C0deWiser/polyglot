<?php

namespace Codewiser\Polyglot\Traits;

use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\PoFileHandler;

trait AsSeparator
{
    protected FileHandlerContract $source;

    public function setSource(string $source)
    {
        $this->source = new PoFileHandler($source);
    }
}