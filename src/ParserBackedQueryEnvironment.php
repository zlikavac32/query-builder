<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

final class ParserBackedQueryEnvironment implements QueryEnvironment
{

    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function queryBuilderFromString(string $sql, ...$parameters): QueryBuilder
    {
        return new ParserBackedQueryBuilder(
            $this->parser,
            $this->parser->parse($sql, $parameters),
        );
    }

    public function queryBuilderFromQuery(Query $query, ...$parameters): QueryBuilder
    {
        return $this->queryBuilderFromString($query->sql(), ...$query->parameters());
    }
}
