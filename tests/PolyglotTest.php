<?php

namespace Tests;

use Codewiser\Polyglot\Polyglot;

class PolyglotTest extends \PHPUnit\Framework\TestCase
{
    public function testIsDotSeparatedKey()
    {
        $this->assertTrue(Polyglot::isDotSeparatedKey('namespace::group.key.dot.separated'));
        $this->assertTrue(Polyglot::isDotSeparatedKey('group.key.dot.separated'));
        $this->assertTrue(Polyglot::isDotSeparatedKey('group.key'));
        $this->assertFalse(Polyglot::isDotSeparatedKey('.group.key'));
        $this->assertFalse(Polyglot::isDotSeparatedKey('group.key.'));
        $this->assertFalse(Polyglot::isDotSeparatedKey('Just a.string'));
    }
}