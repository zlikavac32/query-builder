<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Ds\Sequence;

final class SectionWithinParenthesis implements Section
{

    private Section $section;

    public function __construct(Section $section)
    {
        $this->section = $section;
    }

    public function chunk(): string
    {
        return sprintf('(%s)', $this->section->chunk());
    }

    public function markers(): Sequence
    {
        return $this->section->markers()->map(fn(array $marker) => [$marker[0], $marker[1] + 1]);
    }
}
