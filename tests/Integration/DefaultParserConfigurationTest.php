<?php

declare(strict_types=1);

namespace Zlikavac32\QueryBuilder\Tests\Integration;

use LogicException;
use PHPUnit\Framework\TestCase;
use Zlikavac32\QueryBuilder\FFIGateway;
use Zlikavac32\QueryBuilder\FFIParserException;
use Zlikavac32\QueryBuilder\FFISqlParser;
use Zlikavac32\QueryBuilder\NonPreloadedFFIGateway;
use Zlikavac32\QueryBuilder\Parser;
use Zlikavac32\QueryBuilder\ParserBackedQueryEnvironment;
use Zlikavac32\QueryBuilder\QueryBuilderException;
use Zlikavac32\QueryBuilder\SectionMap;
use Zlikavac32\QueryBuilder\StatementSection;
use Zlikavac32\QueryBuilder\StaticSection;

final class DefaultParserConfigurationTest extends TestCase
{

    private static ?FFIGateway $ffi = null;
    private ?ParserBackedQueryEnvironment $environment = null;

    public static function setUpBeforeClass()
    {
        self::$ffi = NonPreloadedFFIGateway::createDefault();
    }

    public static function tearDownAfterClass()
    {
        self::$ffi = null;
    }

    public function setUp()
    {
        $this->environment = new ParserBackedQueryEnvironment(
            new FFISqlParser(
                self::$ffi
            )
        );
    }

    protected function tearDown()
    {
        $this->environment = null;
    }

    /**
     * @test
     */
    public function parse_exception_is_detected(): void
    {
        $this->expectException(FFIParserException::class);
        $this->expectExceptionMessage('Parsing failed with PARSE_INVALID_SYNTAX');

        $this->environment->queryBuilderFromString('SELECT 1, ');
    }

    /**
     * @test
     */
    public function select_can_be_changed(): void
    {
        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar')
                                   ->select('a')
                                   ->build();

        self::assertSame('SELECT a FROM bar', $query->sql());
        self::assertSame([], $query->parameters()->toArray());
    }

    /**
     * @test
     */
    public function where_can_be_changed(): void
    {
        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar WHERE t = 10')
                                   ->where('b = 12')
                                   ->build();

        self::assertSame('SELECT foo FROM bar WHERE b = 12', $query->sql());
        self::assertSame([], $query->parameters()->toArray());
    }

    /**
     * @test
     */
    public function where_can_be_appended(): void
    {
        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar WHERE t = 10')
                                   ->andWhere('b = 12')
                                   ->build();

        self::assertSame('SELECT foo FROM bar WHERE (t = 10) AND (b = 12)', $query->sql());
        self::assertSame([], $query->parameters()->toArray());
    }

    /**
     * @test
     */
    public function group_by_can_be_changed(): void
    {
        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar GROUP BY t')
                                   ->groupBy('b')
                                   ->build();

        self::assertSame('SELECT foo FROM bar GROUP BY b', $query->sql());
        self::assertSame([], $query->parameters()->toArray());
    }

    /**
     * @test
     */
    public function group_by_can_be_appended(): void
    {
        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar GROUP BY t')
                                   ->andGroupBy('b')
                                   ->build();

        self::assertSame('SELECT foo FROM bar GROUP BY t, b', $query->sql());
        self::assertSame([], $query->parameters()->toArray());
    }

    /**
     * @test
     */
    public function order_by_can_be_changed(): void
    {
        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar ORDER BY t')
                                   ->orderBy('b')
                                   ->build();

        self::assertSame('SELECT foo FROM bar ORDER BY b', $query->sql());
        self::assertSame([], $query->parameters()->toArray());
    }

    /**
     * @test
     */
    public function order_by_can_be_appended(): void
    {
        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar ORDER BY t')
                                   ->andOrderBy('b')
                                   ->build();

        self::assertSame('SELECT foo FROM bar ORDER BY t, b', $query->sql());
        self::assertSame([], $query->parameters()->toArray());
    }

    /**
     * @test
     */
    public function join_can_be_appended(): void
    {
        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar WHERE t = 1')
                                   ->join('other ON k = f')
                                   ->build();

        self::assertSame('SELECT foo FROM bar   JOIN other ON k = f WHERE t = 1', $query->sql());
        self::assertSame([], $query->parameters()->toArray());

        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar JOIN some ON b = 2 WHERE t = 1')
                                   ->join('other ON k = f')
                                   ->build();

        self::assertSame('SELECT foo FROM bar JOIN some ON b = 2   JOIN other ON k = f WHERE t = 1', $query->sql());
        self::assertSame([], $query->parameters()->toArray());
    }

    /**
     * @test
     */
    public function left_can_be_appended(): void
    {
        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar WHERE t = 1')
                                   ->leftJoin('other ON k = f')
                                   ->build();

        self::assertSame('SELECT foo FROM bar  LEFT JOIN other ON k = f WHERE t = 1', $query->sql());
        self::assertSame([], $query->parameters()->toArray());

        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar JOIN some ON b = 2 WHERE t = 1')
                                   ->leftJoin('other ON k = f')
                                   ->build();

        self::assertSame('SELECT foo FROM bar JOIN some ON b = 2  LEFT JOIN other ON k = f WHERE t = 1', $query->sql());
        self::assertSame([], $query->parameters()->toArray());
    }

    /**
     * @test
     */
    public function right_join_can_be_appended(): void
    {
        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar WHERE t = 1')
                                   ->rightJoin('other ON k = f')
                                   ->build();

        self::assertSame('SELECT foo FROM bar  RIGHT JOIN other ON k = f WHERE t = 1', $query->sql());
        self::assertSame([], $query->parameters()->toArray());

        $query = $this->environment->queryBuilderFromString('SELECT foo FROM bar JOIN some ON b = 2 WHERE t = 1')
                                   ->rightJoin('other ON k = f')
                                   ->build();

        self::assertSame(
            'SELECT foo FROM bar JOIN some ON b = 2  RIGHT JOIN other ON k = f WHERE t = 1', $query->sql()
        );
        self::assertSame([], $query->parameters()->toArray());
    }

    /**
     * @test
     */
    public function to_few_parameters_provided_is_reported(): void
    {
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('To few parameters provided');

        $this->environment->queryBuilderFromString('SELECT ?');
    }

    /**
     * @test
     */
    public function to_manny_parameters_provided_is_reported(): void
    {
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('To manny parameters provided');

        $this->environment->queryBuilderFromString('SELECT ?', 2, 3);
    }

    /**
     * @test
     */
    public function parameters_are_properly_replaced(): void
    {
        $sql = <<<'SQL'
SELECT ALL SUM(t.views) + ?
FROM (SELECT * FROM article WHERE author = ?) t
WHERE t.published < ? AND t.status = "PUBLISHED"
GROUP BY t.author
HAVING SUM(t.views) + ? > 0
ORDER BY SUM(t.views) + 1 > 0, ?
INTO @sum
SQL;

        $query = $this->environment->queryBuilderFromString($sql, 1, 2, 3, 4, 5)
                                   ->andWhere('t.premium = ?', 6)
                                   ->leftJoin('some_table st ON st.v = g AND st.u = ?', 7)
                                   ->andWhere('st.v IS NOT NULL')
                                   ->build();

        $expectedSql =
            'SELECT ALL SUM(t.views) + ? '
            . 'FROM (SELECT * FROM article WHERE author = ?) t  '
            . 'LEFT JOIN some_table st ON st.v = g AND st.u = ? '
            . 'WHERE (t.published < ? AND t.status = "PUBLISHED") AND (t.premium = ?) AND (st.v IS NOT NULL) '
            . 'GROUP BY t.author HAVING SUM(t.views) + ? > 0 '
            . 'ORDER BY SUM(t.views) + 1 > 0, ? '
            . 'INTO @sum';

        self::assertSame($expectedSql, $query->sql());
        self::assertSame([1, 2, 7, 3, 6, 4, 5], $query->parameters()->toArray());
    }

    /**
     * @test
     */
    public function parser_is_not_called_when_section_has_no_placeholders(): void
    {
        $environment = new ParserBackedQueryEnvironment(
            new class implements Parser {

                /**
                 * @inheritDoc
                 */
                public function parse(string $content, array $parameters): SectionMap
                {
                    if ($content === 'SELECT 1') {
                        $sectionMap = new SectionMap();
                        $sectionMap->setSectionFor(StatementSection::COLUMNS(), new StaticSection('1'));
                        return $sectionMap;
                    }
                    throw new LogicException('Should not be called');
                }
            }
        );

        $qb = $environment->queryBuilderFromString('SELECT 1');

        $query = $qb->select('col')
            ->join('t1 ON t1c1 = t1c2')
            ->leftJoin('t2 ON t2c1 = t2c2')
            ->rightJoin('t3 ON t3c1 = t3c2')
            ->where('1')
            ->andWhere('2')
            ->groupBy('c1')
            ->andGroupBy('c2')
            ->having('3')
            ->andHaving('4')
            ->orderBy('c4')
            ->andOrderBy('c5')
            ->build();

        self::assertSame(
            'SELECT col FROM    JOIN t1 ON t1c1 = t1c2  LEFT JOIN t2 ON t2c1 = t2c2  RIGHT JOIN t3 ON t3c1 = t3c2 WHERE (1) AND (2) GROUP BY c1, c2 HAVING (3) AND (4) ORDER BY c4, c5',
            $query->sql()
        );
    }
}
