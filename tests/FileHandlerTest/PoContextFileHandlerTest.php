<?php

namespace Tests\FileHandlerTest;

use Codewiser\Polyglot\Contracts\ExtractorContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\JsonFileHandler;
use Codewiser\Polyglot\FileSystem\PhpFileHandler;
use Codewiser\Polyglot\Xgettext\XgettextExtractor;
use Illuminate\Filesystem\Filesystem;
use Sepia\PoParser\Catalog\Entry;

class PoContextFileHandlerTest extends PoFileHandlerTest
{
    protected function getKey()
    {
        return [
            'msgid' => 'My test string',
            'context' => 'My context'
        ];
    }
}