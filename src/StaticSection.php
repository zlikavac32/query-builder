<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Ds\Sequence;
use Ds\Vector;

final class StaticSection implements Section
{

    private string $chunk;
    private $markers;

    public function __construct(string $chunk, ?Sequence $markers = null)
    {
        $this->chunk = $chunk;
        $this->markers = $markers ?? new Vector();
    }

    public function chunk(): string
    {
        return $this->chunk;
    }

    public function markers(): Sequence
    {
        return $this->markers;
    }
}
