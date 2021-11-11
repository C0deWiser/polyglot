<?php

namespace Codewiser\Polyglot\Contracts;

use Illuminate\Support\Collection;

/**
 * @mixin Collection
 */
interface StringsCollectionInterface
{
    /**
     * Prepare data for api response.
     *
     * @return StringsCollectionInterface
     */
    public function api(): StringsCollectionInterface;

    /**
     * Get collection statistics.
     *
     * @return StringsStatisticsContract
     */
    public function statistics(): StringsStatisticsContract;

    /**
     * Get strings with translation.
     *
     * @return StringsCollectionInterface
     */
    public function translated(): StringsCollectionInterface;

    /**
     * Get strings without translation.
     *
     * @return StringsCollectionInterface
     */
    public function untranslated(): StringsCollectionInterface;

    /**
     * Get fuzzy strings.
     *
     * @return StringsCollectionInterface
     */
    public function fuzzy(): StringsCollectionInterface;
}