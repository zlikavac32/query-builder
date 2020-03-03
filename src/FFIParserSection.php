<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Ds\Sequence;
use Ds\Vector;

final class FFIParserSection implements Section
{

    private string $chunk;
    private Sequence $markers;

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
        return $this->markers->map(fn(array $marker) => [$marker[1], $marker[0]]);
    }

    public function copy(): Section
    {
        return $this;
    }
}
