<?php

namespace Codewiser\Polyglot\Contracts;


use Codewiser\Polyglot\Collections\FileCollection;
use Codewiser\Polyglot\FileSystem\Contracts\FileHandlerContract;

/**
 * Populates extracted strings through every locale.
 */
interface ProducerContract
{
    /**
     * Set the directory to store lang files.
     *
     * @param string $storage
     */
    public function setStorage(string $storage): void;

    /**
     * Set the array of locales.
     *
     * @param array $locales
     */
    public function setLocales(array $locales): void;

    /**
     * Set the filename with the previously extracted strings.
     *
     * @param FileHandlerContract $source
     */
    public function setSource(FileHandlerContract $source): void;

    /**
     * Populate extracted strings through every locale.
     *
     * @param  null|array  $locales  *
     *
     * @return bool
     */
    public function produce(?array $locales = null): bool;

    /**
     * Get processed files.
     *
     * @return FileCollection|FileHandlerContract[]
     */
    public function getPopulated(): FileCollection;
}