<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Ds\Sequence;
use Ds\Vector;

final class Query
{

    private string $sql;
    private Sequence $parameters;

    public function __construct(string $sql, ?Sequence $parameters = null)
    {
        $this->sql = $sql;
        $this->parameters = $parameters ?? new Vector();
    }

    public function sql(): string
    {
        return $this->sql;
    }

    public function parameters(): Sequence
    {
        return $this->parameters;
    }

    public static function create(string $sql, ...$parameters): Query
    {
        return new Query($sql, new Vector($parameters));
    }
}
