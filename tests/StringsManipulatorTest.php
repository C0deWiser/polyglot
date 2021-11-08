<?php

namespace Tests;

use Codewiser\Polyglot\StringsCollector;
use Codewiser\Polyglot\StringsManipulator;
use Monolog\Test\TestCase;

class StringsManipulatorTest extends TestCase
{
    protected $pot = __DIR__ . '/resources/lang/messages.pot';
    protected StringsCollector $collector;
    protected StringsManipulator $manipulator;

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

        $this->manipulator = new StringsManipulator(
            ['en'],
            __DIR__ . '/resources/lang',
            $this->collector
        );

        parent::setUp();
    }

    public function testPopulate()
    {
        $this->manipulator->populate(
            $this->collector->getPortableObjectTemplate()
        );

        $this->assertTrue(file_exists($this->manipulator->getJsonFile('en')));
        $this->assertTrue(file_exists($this->manipulator->getPhpFile('en', 'short')));
    }
}