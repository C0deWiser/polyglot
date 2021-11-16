<?php

namespace Tests\FileHandlerTest;

use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\JsonFileHandler;
use Sepia\PoParser\Catalog\Entry;

class JsonFileHandlerTest extends FileHandlerTest
{
    protected function getFile(): FileHandlerContract
    {
        $json = [
            'Key' => 'Value',
            'Request' => 'Response'
        ];

        $filename = $this->temp_path . '/test.json';
        file_put_contents($filename, json_encode($json));

        return new JsonFileHandler($filename);
    }

    protected function getKey()
    {
        return 'Test key';
    }

    protected function getValue()
    {
        return 'Test value';
    }

    protected function assertEntry($expected, Entry $fetched)
    {
        $this->assertEquals($expected, $fetched->getMsgStr());
    }
}