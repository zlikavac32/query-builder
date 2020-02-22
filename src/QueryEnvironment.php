<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

interface QueryEnvironment
{

    public function queryBuilderFromString(string $sql, ...$parameters): QueryBuilder;

    public function queryBuilderFromQuery(Query $query, ...$parameters): QueryBuilder;
}
