<?php

namespace Tests;

class GettextManipulatorTest extends WithGettextManipulator
{
    public function testPopulate()
    {
        $this->gettextManipulator->populate(
            $this->extractor->getPortableObjectTemplate()
        );

        $this->assertTrue(file_exists($this->gettextManipulator->getPortableObject('en', 'LC_MESSAGES', 'messages')));

        $this->gettextManipulator->compile();
        $this->assertTrue(file_exists($this->gettextManipulator->getMachineObject('en', 'LC_MESSAGES', 'messages')));
    }
}