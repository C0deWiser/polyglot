<?php

namespace Tests;

trait Environment
{
    protected string $base_path = __DIR__;
    protected string $sources_path = __DIR__ . '/sources';
    protected string $temp_path = __DIR__ . '/tmp';
    protected string $output_path = __DIR__ . '/tmp/output';
    protected string $context_path = __DIR__ . '/sources/php/context.php';
}