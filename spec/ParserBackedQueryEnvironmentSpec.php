<?php

declare(strict_types=1);

namespace spec\Zlikavac32\QueryBuilder;

use Ds\Vector;
use PhpSpec\ObjectBehavior;
use Zlikavac32\QueryBuilder\Parser;
use Zlikavac32\QueryBuilder\ParserBackedQueryBuilder;
use Zlikavac32\QueryBuilder\ParserBackedQueryEnvironment;
use Zlikavac32\QueryBuilder\Query;
use Zlikavac32\QueryBuilder\SectionMap;

final class ParserBackedQueryEnvironmentSpec extends ObjectBehavior
{

    public function let(Parser $parser): void
    {
        $this->beConstructedWith($parser);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ParserBackedQueryEnvironment::class);
    }

    public function it_should_create_query_builder_from_string(Parser $parser): void
    {
        $parser->parse('foo', [1, 2])->willReturn(new SectionMap());

        $this->queryBuilderFromString('foo', 1, 2)->shouldBeAnInstanceOf(ParserBackedQueryBuilder::class);
    }

    public function it_should_create_query_builder_query(Parser $parser): void
    {
        $parser->parse('foo', [1, 2])->willReturn(new SectionMap());

        $this->queryBuilderFromQuery(new Query('foo', new Vector([1, 2])))->shouldBeAnInstanceOf(
            ParserBackedQueryBuilder::class
        );
    }
}
