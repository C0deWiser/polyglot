<?php

namespace Tests\FileHandlerTest;

use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;
use Codewiser\Polyglot\FileSystem\JsonFileHandler;
use Codewiser\Polyglot\FileSystem\PhpFileHandler;
use Sepia\PoParser\Catalog\Entry;

class PhpFileHandlerTest extends FileHandlerTest
{
    protected function getFile(): FileHandlerContract
    {
        $data = [
            'key' => 'Value',
            'request' => 'Response',
            'data' => [
                'one' => '',
                'two' => ''
            ]
        ];

        $filename = $this->temp_path . '/test.php';
        $content = var_export($data, true);

        file_put_contents($filename, "<?php\nreturn " . $content . ';');

        return new PhpFileHandler($filename);
    }

    protected function getKey()
    {
        return 'data.test';
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