<?php

namespace Tests;

use Codewiser\Polyglot\GettextPopulator;
use Codewiser\Polyglot\StringsCollector;
use Codewiser\Polyglot\StringsPopulator;
use Monolog\Test\TestCase;

class GettextCollectorTest extends TestCase
{
    protected $pot = __DIR__ . '/resources/lang/test.pot';
    protected StringsCollector $collector;
    protected StringsPopulator $passthroughsPopulator;
    protected GettextPopulator $populator;

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

        $this->passthroughsPopulator = new StringsPopulator(
            ['en'],
            __DIR__ . '/resources/lang',
            $this->collector
        );

        $this->populator = new GettextPopulator(
            ['en'],
            __DIR__ . '/resources/gettext',
            __DIR__ . '/resources/gettext',
            'messages',
            $this->passthroughsPopulator
        );
        $this->populator->setPassthroughs(['short.']);

        parent::setUp();
    }

    public function testPopulate()
    {
        $this->populator->populate(
            $this->collector->getPortableObjectTemplate()
        );

        $this->assertTrue(file_exists($this->populator->getPortableObject('en', 'LC_MESSAGES', 'messages')));

        $this->populator->compile();
        $this->assertTrue(file_exists($this->populator->getMachineObject('en', 'LC_MESSAGES', 'messages')));
    }
}