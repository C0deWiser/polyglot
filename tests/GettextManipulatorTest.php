<?php

namespace Tests;

use Codewiser\Polyglot\GettextManipulator;
use Codewiser\Polyglot\StringsCollector;
use Codewiser\Polyglot\StringsManipulator;
use Monolog\Test\TestCase;

class GettextManipulatorTest extends TestCase
{
    protected $pot = __DIR__ . '/resources/lang/messages.pot';
    protected StringsCollector $collector;
    protected StringsManipulator $stringsManipulator;
    protected GettextManipulator $manipulator;

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

        $this->stringsManipulator = new StringsManipulator(
            ['en'],
            __DIR__ . '/resources/lang',
            $this->collector
        );

        $this->manipulator = new GettextManipulator(
            __DIR__ . '/resources/lang',
            __DIR__ . '/resources/lang',
            'messages',
            $this->stringsManipulator
        );
        $this->manipulator->setPassthroughs(['short.']);

        parent::setUp();
    }

    public function testPopulate()
    {
        $this->manipulator->populate(
            $this->collector->getPortableObjectTemplate()
        );

        $this->assertTrue(file_exists($this->manipulator->getPortableObject('en', 'LC_MESSAGES', 'messages')));

        $this->manipulator->compile();
        $this->assertTrue(file_exists($this->manipulator->getMachineObject('en', 'LC_MESSAGES', 'messages')));
    }
}