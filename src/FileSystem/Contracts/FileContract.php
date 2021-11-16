<?php

namespace Codewiser\Polyglot\FileSystem\Contracts;

use Codewiser\Polyglot\FileSystem\Contracts\ResourceContract;

interface FileContract extends ResourceContract
{

    /**
     * Get the name part of filename.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Copy this file(?).
     *
     * @param string $to
     * @return bool
     */
    public function copyTo(string $to): bool;

    /**
     * Get file extension.
     *
     * @return string
     */
    public function extension(): string;

    /**
     * Write the contents of a file.
     *
     * @param string $contents
     * @param bool $lock
     * @return int|bool
     */
    public function putContent(string $contents, bool $lock = false);

    /**
     * Get the contents of a file.
     *
     * @param bool $lock
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getContent(bool $lock = false): string;
}