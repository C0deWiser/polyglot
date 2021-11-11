<?php

namespace Codewiser\Polyglot\Collections;

class FilesCollection extends \Illuminate\Support\Collection
{
    public function lastModified()
    {
        $this->sort(function ($filename) {
            return filemtime($filename);
        });
    }
}