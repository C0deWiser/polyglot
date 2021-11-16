<?php

namespace Codewiser\Polyglot\FileSystem\Traits;

use Codewiser\Polyglot\Contracts\StatisticsContract;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;

trait HasStatistics
{
    public function statistics(): StatisticsContract
    {
        return $this->allEntries()->statistics();
    }
}