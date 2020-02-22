<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

interface CompositeSection extends Section
{

    public function append(Section $section, string $glue): void;
}
