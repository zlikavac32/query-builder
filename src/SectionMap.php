<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder;

use Ds\Map;
use Ds\Sequence;
use Ds\Vector;

final class SectionMap
{

    /**
     * @var Section[]|Map
     */
    private Map $sections;

    public function __construct()
    {
        $this->sections = new Map();
    }

    public function hasSectionFor(StatementSection $enum): bool
    {
        return $this->sections->hasKey($enum->name());
    }

    public function sectionFor(StatementSection $enum): Section
    {
        if (!$this->hasSectionFor($enum)) {
            throw new QueryBuilderException(sprintf('Section %s not found', $enum->name()));
        }

        return $this->sections->get($enum->name());
    }

    public function setSectionFor(StatementSection $enum, Section $section): void
    {
        $this->sections->put($enum->name(), $section);
    }

    public function appendSectionTo(StatementSection $enum, Section $section, string $glue): void
    {
        if (!$this->hasSectionFor($enum)) {
            $this->setSectionFor($enum, $section);

            return;
        }

        $currentSection = $this->sectionFor($enum);

        if (!$currentSection instanceof CompositeSection) {
            if (
                $enum === StatementSection::WHERE()
                || $enum === StatementSection::HAVING()
            ) {
                $currentSection = new SectionWithinParenthesis($currentSection);
            }

            $currentSection = new StaticCompositeSection($currentSection);
            $this->setSectionFor($enum, $currentSection);
        }

        $currentSection->append($section, $glue);
    }

    public function buildSql(): string
    {
        $this->assertHaveAtLeastOneSection();

        $parts = [
            [null, StatementSection::MODIFIERS()],
            [null, StatementSection::COLUMNS()],
            [null, StatementSection::FIRST_INTO()],
            ['FROM', StatementSection::TABLES()],
            ['PARTITION', StatementSection::PARTITION()],
            ['WHERE', StatementSection::WHERE()],
            ['GROUP BY', StatementSection::GROUP_BY()],
            ['HAVING', StatementSection::HAVING()],
            ['ORDER BY', StatementSection::ORDER_BY()],
            ['LIMIT', StatementSection::LIMIT()],
            ['PROCEDURE', StatementSection::PROCEDURE()],
            [null, StatementSection::SECOND_INTO()],
            [null, StatementSection::FLAGS()],
        ];

        $partsAsString[] = 'SELECT';

        foreach ($parts as [$glue, $component]) {
            if (!$this->hasSectionFor($component)) {
                continue;
            }

            $section = $this->sectionFor($component);

            if ($glue !== null) {
                $partsAsString[] = ' ' . $glue;
            }

            $chunk = $section->chunk();
            $placeHolderOffset = 0;
            foreach ($section->markers() as [$parameter, $placeholderPosition]) {
                if (!$parameter instanceof InlineValue) {
                    continue;
                }

                $placeholderPosition += $placeHolderOffset;

                $chunk = substr($chunk, 0, $placeholderPosition) . '(' . $parameter->sql() . ')' . substr(
                        $chunk, $placeholderPosition + 1
                    );
                $placeHolderOffset += strlen($parameter->sql()) - 1 + 2;
            }

            $partsAsString[] = " " . $chunk;
        }

        return implode('', $partsAsString);
    }

    public function buildParameters(): Sequence
    {
        $this->assertHaveAtLeastOneSection();

        $order = [
            StatementSection::MODIFIERS(),
            StatementSection::COLUMNS(),
            StatementSection::FIRST_INTO(),
            StatementSection::TABLES(),
            StatementSection::PARTITION(),
            StatementSection::WHERE(),
            StatementSection::GROUP_BY(),
            StatementSection::HAVING(),
            StatementSection::ORDER_BY(),
            StatementSection::LIMIT(),
            StatementSection::PROCEDURE(),
            StatementSection::SECOND_INTO(),
            StatementSection::FLAGS(),
        ];

        $ret = new Vector();

        foreach ($order as $component) {
            if (!$this->hasSectionFor($component)) {
                continue;
            }

            $section = $this->sectionFor($component);

            $ret = $section->markers()->map(fn(array $parameter) => $parameter[0])->reduce(
                function (Sequence $all, $parameter): Sequence {
                    if ($parameter instanceof InlineValue) {
                        foreach ($parameter->parameters() as $param) {
                            $all->push($param);
                        }

                        return $all;
                    }

                    $all->push($parameter);

                    return $all;
                }, $ret
            );
        }

        return $ret;
    }

    private function assertHaveAtLeastOneSection()
    {
        if ($this->sections->isEmpty()) {
            throw new QueryBuilderException('No section provided yet');
        }
    }

    public function copy(): SectionMap
    {
        $copy = new SectionMap();

        foreach ($this->sections as $key => $value) {
            $copy->sections->put($key, $value->copy());
        }

        return $copy;
    }
}