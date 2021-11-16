<?php

namespace Tests\FileHandlerTest;

use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use PHPUnit\Framework\TestCase;
use Sepia\PoParser\Catalog\Entry;
use Tests\Environment;

abstract class FileHandlerTest extends TestCase
{
    use Environment;

    /**
     * Get FileHandler instance to run tests.
     *
     * @return FileHandlerContract
     */
    abstract protected function getFile(): FileHandlerContract;

    /**
     * Get entry key to test get and put.
     *
     * @return mixed
     */
    abstract protected function getKey();

    /**
     * Get entry value to test put and get.
     *
     * @return mixed
     */
    abstract protected function getValue();

    /**
     * Assert fetched entry is the same with expected.
     *
     * @param mixed $expected
     * @param Entry $fetched
     * @return mixed
     */
    abstract protected function assertEntry($expected, Entry $fetched);

    public function testExists()
    {
        $this->assertTrue($this->getFile()->exists());
    }

    public function testDelete()
    {
        $file = $this->getFile();

        $this->assertTrue($file->delete());
        $this->assertFalse($file->exists());
    }

    public function testPutAndGet()
    {
        $file = $this->getFile();
        $count = $file->allEntries()->count();

        $this->assertTrue($file->putEntry($this->getKey(), $this->getValue()));
        $this->assertEquals($count + 1, $file->allEntries()->count());

        $fetched = $file->getEntry($this->getKey());

        $this->assertNotNull($fetched);
        $this->assertEntry($this->getValue(), $fetched);
    }

    public function testListings()
    {
        $file = $this->getFile();
        $count = $file->allEntries()->count();

        $file->putEntry($this->getKey(), $this->getValue());
        $fetched = $file->getEntry($this->getKey());

        $this->assertNotNull($file->allEntries()->exists($fetched));
        $this->assertEquals($count + 1, $file->allEntries()->count());
    }

    public function testRemove()
    {
        $file = $this->getFile();
        $file->putEntry($this->getKey(), $this->getValue());

        $count = $file->allEntries()->count();
        $file->removeEntry($this->getKey());

        $this->assertEquals($count - 1, $file->allEntries()->count());
        $this->assertNull($file->getEntry($this->getKey()));
    }
}