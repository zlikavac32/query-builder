<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Ds\Sequence;

interface Section
{

    public function chunk(): string;

    /**
     * Returns a list of parameter markers used within a section. List must be sorted and contain
     * one array value for every parameter marker. First element of that array is parameter value
     * and second is parameter marker byte offset within the chunk.
     *
     * For a chunk "a = ? AND b = ?" and values [2, 3], this methods should return a list with
     * values [2, 4] and [3, 14].
     *
     * @return Sequence
     */
    public function markers(): Sequence;
}
