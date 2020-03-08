<?php

declare(strict_types=1);

namespace spec\Zlikavac32\QueryBuilder;

use Ds\Vector;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Zlikavac32\QueryBuilder\Query;
use Zlikavac32\QueryBuilder\QueryBuilderException;
use Zlikavac32\QueryBuilder\SectionMap;
use Zlikavac32\QueryBuilder\StatementSection;
use Zlikavac32\QueryBuilder\StaticSection;

final class SectionMapSpec extends ObjectBehavior
{

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SectionMap::class);
    }

    public function it_should_throw_exception_when_building_empty_map(): void
    {
        $this->shouldThrow(QueryBuilderException::class)->duringBuildSql();
        $this->shouldThrow(QueryBuilderException::class)->duringBuildParameters();
    }

    public function it_should_set_section(): void
    {
        $this->setSectionFor(
            StatementSection::COLUMNS(), new StaticSection(
                'v + ?', new Vector(
                    [
                        [5, 4],
                    ]
                )
            )
        );

        $this->shouldBuildSql('SELECT v + ?', 5);

        $this->setSectionFor(
            StatementSection::COLUMNS(), new StaticSection('id, name')
        );

        $this->shouldBuildSql('SELECT id, name');

        $this->setSectionFor(
            StatementSection::TABLES(), new StaticSection('user')
        );

        $this->shouldBuildSql('SELECT id, name FROM user');
    }

    public function it_should_append_to_empty_section(): void
    {
        $this->appendSectionTo(
            StatementSection::COLUMNS(), new StaticSection(
            'v + ?', new Vector(
                [
                    [5, 4],
                ]
            )
        ), ' should-be-ignored '
        );

        $this->shouldBuildSql('SELECT v + ?', 5);
    }

    public function it_should_append_to_existing_section(): void
    {
        $this->setSectionFor(
            StatementSection::COLUMNS(), new StaticSection('id, name')
        );
        $this->appendSectionTo(
            StatementSection::COLUMNS(), new StaticSection('money, ?', new Vector([[5, 7]])), ', '
        );

        $this->shouldBuildSql('SELECT id, name, money, ?', 5);
    }

    public function it_should_use_parenthesis_on_where(): void
    {
        $this->setSectionFor(StatementSection::WHERE(), new StaticSection('a = 1'));

        $this->shouldBuildSql('SELECT WHERE a = 1');

        $this->appendSectionTo(
            StatementSection::WHERE(), new StaticSection('b = 1'), ' AND '
        );

        $this->shouldBuildSql('SELECT WHERE (a = 1) AND b = 1');
    }

    public function it_should_inject_subquery(): void
    {
        $this->setSectionFor(
            StatementSection::WHERE(),
            new StaticSection(
                'a = ?', new Vector([[new Query('SELECT ?', new Vector([10])), 4]])
            )
        );

        $this->shouldBuildSql('SELECT WHERE a = (SELECT ?)', 10);

        $this->appendSectionTo(
            StatementSection::WHERE(),
            new StaticSection(
                'b = ?', new Vector([[new Query('SELECT ?', new Vector([20])), 4]])
            ),
            ' AND '
        );

        $this->shouldBuildSql('SELECT WHERE (a = (SELECT ?)) AND b = (SELECT ?)', 10, 20);

        $this->appendSectionTo(
            StatementSection::WHERE(),
            new StaticSection(
                'c = ?', new Vector([[new Query('SELECT ?', new Vector([30])), 4]])
            ),
            ' AND '
        );

        $this->shouldBuildSql('SELECT WHERE (a = (SELECT ?)) AND b = (SELECT ?) AND c = (SELECT ?)', 10, 20, 30);
    }

    public function it_should_return_requested_section(): void
    {
        $section = new StaticSection('id, name');

        $this->setSectionFor(StatementSection::COLUMNS(), $section);

        $this->sectionFor(StatementSection::COLUMNS())->shouldReturn($section);
    }

    public function it_should_throw_exception_when_requested_section_does_not_exist(): void
    {
        $this->shouldThrow(QueryBuilderException::class)->duringSectionFor(StatementSection::COLUMNS());
    }

    public function it_should_say_whether_section_exists_or_not(): void
    {
        $this->hasSectionFor(StatementSection::COLUMNS())->shouldReturn(false);

        $this->setSectionFor(StatementSection::COLUMNS(), new StaticSection('id, name'));

        $this->hasSectionFor(StatementSection::COLUMNS())->shouldReturn(true);
        $this->hasSectionFor(StatementSection::TABLES())->shouldReturn(false);
    }

    public function it_should_copy_itself(): void
    {
        $this->setSectionFor(StatementSection::COLUMNS(), new StaticSection('1'));

        $copy = $this->copy();

        $this->setSectionFor(StatementSection::TABLES(), new StaticSection('t1'));
        $copy->setSectionFor(StatementSection::TABLES(), new StaticSection('t2'));

        $this->shouldBuildSql('SELECT 1 FROM t1');
        $copy->shouldBuildSql('SELECT 1 FROM t2');
    }

    public function getMatchers(): array
    {
        return [
            'buildSql' => function (SectionMap $sectionMap, string $sql, ...$params): bool {
                $builtSql = $sectionMap->buildSql();

                if ($builtSql !== $sql) {
                    throw new FailureException(sprintf('Expected sql "%s" != got "%s"', $sql, $builtSql));
                }

                $builtParams = $sectionMap->buildParameters()->toArray();

                if ($builtParams != $params) {
                    throw new FailureException(
                        sprintf('Expected params %s != got %s', json_encode($params), json_encode($builtParams))
                    );
                }

                return true;
            },
        ];
    }
}
