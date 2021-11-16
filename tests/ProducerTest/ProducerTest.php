<?php

namespace Tests\ProducerTest;

use Codewiser\Polyglot\Contracts\ProducerContract;
use Codewiser\Polyglot\Contracts\SeparatorContract;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Tests\Environment;

abstract class ProducerTest extends TestCase
{
    use Environment;

    /**
     * Setup processor instance to run tests.
     *
     * @return ProducerContract
     */
    abstract public function getDotSeparatedProducer(): ProducerContract;

    /**
     * Setup processor instance to run tests.
     *
     * @return ProducerContract
     */
    abstract public function getNaturalStringsProducer(): ProducerContract;

    /**
     * Setup separator instance to get separated files.
     *
     * @return SeparatorContract
     */
    abstract protected function getSeparator(): SeparatorContract;

    protected function setUp(): void
    {
        parent::setUp();

        $filesystem = new Filesystem();
        $filesystem->cleanDirectory($this->temp_path);
    }

    public function testDotSeparatedPopulation()
    {
        $producer = $this->getDotSeparatedProducer();

        $producer->setSource($this->getSeparator()->getExtractedKeys());
        $producer->setStorage($this->output_path);
        $producer->setLocales(['en', 'ru', 'de']);

        $this->assertTrue($producer->produce());

        $this->assertCount(3, $producer->getPopulated());

        foreach ($producer->getPopulated() as $populated) {
            $this->assertEquals(
                $this->getSeparator()->getExtractedKeys()->allEntries()->count(),
                $populated->allEntries()->count()
            );
        }
    }

    public function testNaturalStringsPopulation()
    {
        $producer = $this->getNaturalStringsProducer();

        $producer->setSource($this->getSeparator()->getExtractedStrings());
        $producer->setStorage($this->output_path);
        $producer->setLocales(['en', 'ru', 'de']);

        $this->assertTrue($producer->produce());

        $this->assertCount(3, $producer->getPopulated());

        foreach ($producer->getPopulated() as $populated) {
            $this->assertEquals(
                $this->getSeparator()->getExtractedStrings()->allEntries()->count(),
                $populated->allEntries()->count()
            );
        }
    }
}