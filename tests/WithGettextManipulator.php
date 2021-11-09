<?php

namespace Tests;

use Codewiser\Polyglot\Manipulators\GettextManipulator;

class WithGettextManipulator extends WithStringsManipulator
{
    protected GettextManipulator $gettextManipulator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gettextManipulator = new GettextManipulator(
            ['en', 'ru'],
            $this->loader,
            $this->stringsManipulator
        );
        $this->gettextManipulator->setPassthroughs(['short.']);

    }
}