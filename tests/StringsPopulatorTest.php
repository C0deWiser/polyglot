<?php

namespace Tests;

use Codewiser\Polyglot\StringsCollector;
use Codewiser\Polyglot\StringsPopulator;
use Monolog\Test\TestCase;

class StringsPopulatorTest extends TestCase
{
    protected $pot = __DIR__ . '/resources/lang/test.pot';
    protected StringsCollector $collector;
    protected StringsPopulator $populator;

    protected function setUp(): void
    {
        $this->collector = new StringsCollector(
            'unit-test',
            __DIR__,
            [__DIR__ . '/sources'],
            $this->pot
        );
        $this->collector->exclude([__DIR__ . '/resources/lang']);
        $this->collector->collect();

        $this->populator = new StringsPopulator(
            ['en'],
            __DIR__ . '/resources/lang',
            $this->collector
        );

        parent::setUp();
    }

    public function testPopulate()
    {
        $this->populator->populate(
            $this->collector->getPortableObjectTemplate()
        );

        $this->assertTrue(file_exists($this->populator->getJsonFile('en')));
        $this->assertTrue(file_exists($this->populator->getPhpFile('en', 'short')));
    }
}