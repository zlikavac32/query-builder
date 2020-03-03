<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

interface QueryBuilder
{

    public function select(string $columns, ...$parameters): QueryBuilder;

    public function andWhere(string $condition, ...$parameters): QueryBuilder;

    public function where(string $condition, ...$parameters): QueryBuilder;

    public function andHaving(string $condition, ...$parameters): QueryBuilder;

    public function having(string $condition, ...$parameters): QueryBuilder;

    public function limit(int $limit, ?int $offset = null): QueryBuilder;

    public function groupBy(string $groupBy, ...$parameters): QueryBuilder;

    public function andGroupBy(string $groupBy, ...$parameters): QueryBuilder;

    public function orderBy(string $orderBy, ...$parameters): QueryBuilder;

    public function andOrderBy(string $orderBy, ...$parameters): QueryBuilder;

    public function join(string $join, ...$parameters): QueryBuilder;

    public function leftJoin(string $join, ...$parameters): QueryBuilder;

    public function rightJoin(string $join, ...$parameters): QueryBuilder;

    public function copy(): QueryBuilder;

    public function build(): Query;
}
