<?php

namespace Tests;

class StringsManipulatorTest extends WithStringsManipulator
{
    public function testPopulate()
    {
        $this->stringsManipulator->populate(
            $this->extractor->getPortableObjectTemplate()
        );

        $this->assertTrue(file_exists($this->stringsManipulator->getJsonFile('en')));
        $this->assertTrue(file_exists($this->stringsManipulator->getPhpFile('en', 'short')));
    }
}