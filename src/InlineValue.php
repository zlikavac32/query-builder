<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Ds\Sequence;

interface InlineValue
{

    public function sql(): string;

    public function parameters(): Sequence;
}
