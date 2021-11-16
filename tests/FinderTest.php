<?php

namespace Tests;

use Codewiser\Polyglot\FileSystem\Contracts\FinderContract;
use Codewiser\Polyglot\FileSystem\Finder;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

class FinderTest extends TestCase
{
    protected FinderContract $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = new Finder(__DIR__ . '/sources', new Filesystem());
    }
}