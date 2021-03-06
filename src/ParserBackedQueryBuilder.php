<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Ds\Sequence;

final class ParserBackedQueryBuilder implements QueryBuilder
{

    private SectionMap $sectionMap;
    private Parser $parser;

    public function __construct(Parser $parser, SectionMap $sectionMap
    ) {
        $this->sectionMap = $sectionMap;
        $this->parser = $parser;
    }

    private function shouldSkipParsing(string $section, array $parameters): bool {
        $noPossiblePlaceholderExists = strpos($section, '?') === false;
        return count($parameters) === 0 && $noPossiblePlaceholderExists;
    }

    public function select(string $columns, ...$parameters): QueryBuilder
    {
        if ($this->shouldSkipParsing($columns, $parameters)) {
            $section = new StaticSection($columns);
        } else {
            $section = $this->resolveSection(
                'SELECT %s', [$columns], $parameters, StatementSection::COLUMNS()
            );
        }

        $this->sectionMap->setSectionFor(StatementSection::COLUMNS(), $section);

        return $this;
    }

    public function andWhere(string $condition, ...$parameters): QueryBuilder
    {
        if ($this->shouldSkipParsing($condition, $parameters)) {
            $section = new StaticSection($condition);
        } else {
            $section = $this->resolveSection(
                'SELECT 1 WHERE %s', [$condition], $parameters, StatementSection::WHERE()
            );
        }

        $this->sectionMap->appendSectionTo(StatementSection::WHERE(), new SectionWithinParenthesis($section), ' AND ');

        return $this;
    }

    public function where(string $condition, ...$parameters): QueryBuilder
    {
        if ($this->shouldSkipParsing($condition, $parameters)) {
            $section = new StaticSection($condition);
        } else {
            $section = $this->resolveSection(
                'SELECT 1 WHERE %s', [$condition], $parameters, StatementSection::WHERE()
            );
        }

        $this->sectionMap->setSectionFor(StatementSection::WHERE(), $section);

        return $this;
    }

    public function andHaving(string $condition, ...$parameters): QueryBuilder
    {
        if ($this->shouldSkipParsing($condition, $parameters)) {
            $section = new StaticSection($condition);
        } else {
            $section = $this->resolveSection(
                'SELECT 1 HAVING %s', [$condition], $parameters, StatementSection::HAVING()
            );
        }

        $this->sectionMap->appendSectionTo(StatementSection::HAVING(), new SectionWithinParenthesis($section), ' AND ');

        return $this;
    }

    public function having(string $condition, ...$parameters): QueryBuilder
    {
        if ($this->shouldSkipParsing($condition, $parameters)) {
            $section = new StaticSection($condition);
        } else {
            $section = $this->resolveSection(
                'SELECT 1 HAVING %s', [$condition], $parameters, StatementSection::HAVING()
            );
        }

        $this->sectionMap->setSectionFor(StatementSection::HAVING(), $section);

        return $this;
    }

    public function limit(int $limit, ?int $offset = null): QueryBuilder
    {
        $this->sectionMap->setSectionFor(
            StatementSection::LIMIT(), new FFIParserSection(
                (string) $limit . ($offset === null ? '' : sprintf('OFFSET %d', $offset))
            )
        );

        return $this;
    }

    public function groupBy(string $groupBy, ...$parameters): QueryBuilder
    {
        if ($this->shouldSkipParsing($groupBy, $parameters)) {
            $section = new StaticSection($groupBy);
        } else {
            $section = $this->resolveSection(
                'SELECT 1 GROUP BY %s', [$groupBy], $parameters, StatementSection::GROUP_BY()
            );
        }

        $this->sectionMap->setSectionFor(StatementSection::GROUP_BY(), $section);

        return $this;
    }

    public function andGroupBy(string $groupBy, ...$parameters): QueryBuilder
    {
        if ($this->shouldSkipParsing($groupBy, $parameters)) {
            $section = new StaticSection($groupBy);
        } else {
            $section = $this->resolveSection(
                'SELECT 1 GROUP BY %s', [$groupBy], $parameters, StatementSection::GROUP_BY()
            );
        }

        $this->sectionMap->appendSectionTo(StatementSection::GROUP_BY(), $section, ', ');

        return $this;
    }

    public function orderBy(string $orderBy, ...$parameters): QueryBuilder
    {
        if ($this->shouldSkipParsing($orderBy, $parameters)) {
            $section = new StaticSection($orderBy);
        } else {
            $section = $this->resolveSection(
                'SELECT 1 ORDER BY %s', [$orderBy], $parameters, StatementSection::ORDER_BY()
            );
        }

        $this->sectionMap->setSectionFor(StatementSection::ORDER_BY(), $section);

        return $this;
    }

    public function andOrderBy(string $orderBy, ...$parameters): QueryBuilder
    {
        if ($this->shouldSkipParsing($orderBy, $parameters)) {
            $section = new StaticSection($orderBy);
        } else {
            $section = $this->resolveSection(
                'SELECT 1 ORDER BY %s', [$orderBy], $parameters, StatementSection::ORDER_BY()
            );
        }

        $this->sectionMap->appendSectionTo(StatementSection::ORDER_BY(), $section, ', ');

        return $this;
    }

    public function join(string $join, ...$parameters): QueryBuilder
    {
        return $this->doJoin($join, $parameters);
    }

    public function leftJoin(string $join, ...$parameters): QueryBuilder
    {
        return $this->doJoin($join, $parameters, 'LEFT');
    }

    public function rightJoin(string $join, ...$parameters): QueryBuilder
    {
        return $this->doJoin($join, $parameters, 'RIGHT');
    }

    private function doJoin(string $join, array $parameters, ?string $type = null
    ): QueryBuilder
    {
        if ($this->shouldSkipParsing($join, $parameters)) {
            $section = new StaticSection(sprintf('  %s JOIN %s', (string) $type, $join));
        } else {
            $section = $this->resolveSection(
                'SELECT 1 FROM t %s JOIN %s', [(string) $type, $join], $parameters,
                StatementSection::TABLES()
            );

            $section = new ReplaceTWithSpaceSection($section);
        }

        $this->sectionMap->appendSectionTo(StatementSection::TABLES(), $section, '');

        return $this;
    }

    public function build(): Query
    {
        return new Query($this->sectionMap->buildSql(), $this->sectionMap->buildParameters());
    }

    private function resolveSection(
        string $format, array $parts, array $parameters, StatementSection $component
    ): Section {
        return $this->parser->parse(
            sprintf($format, ...$parts),
            $parameters
        )->sectionFor($component);
    }

    public function copy(): QueryBuilder
    {
        return new ParserBackedQueryBuilder($this->parser, $this->sectionMap->copy());
    }
}

/**
 * Instead of removing "t" from the "FROM" part and then adjusting all of the
 * placeholders, "t" is just replaced by the white space
 *
 * @internal
 */
class ReplaceTWithSpaceSection implements Section {

    /**
     * @var Section
     */
    private Section $section;

    public function __construct(Section $section)
    {
        $this->section = $section;
    }

    public function chunk(): string
    {
        $chunk = $this->section->chunk();
        $chunk[0] = ' ';

        return $chunk;
    }

    public function markers(): Sequence
    {
        return $this->section->markers();
    }

    public function copy(): Section
    {
        return new ReplaceTWithSpaceSection($this->section->copy());
    }
}
