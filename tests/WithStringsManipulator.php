<?php

namespace Tests;

use Codewiser\Polyglot\Extractor;
use Codewiser\Polyglot\Manipulators\StringsManipulator;

class WithStringsManipulator extends WithExtractor
{
    protected StringsManipulator $stringsManipulator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor->extract();

        $this->stringsManipulator = new StringsManipulator(
            ['en', 'ru'],
            $this->loader
        );
    }
}