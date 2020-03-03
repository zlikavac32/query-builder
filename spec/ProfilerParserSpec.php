<?php

declare(strict_types=1);

namespace spec\Zlikavac32\QueryBuilder;

use LogicException;
use PhpSpec\ObjectBehavior;
use Zlikavac32\QueryBuilder\Parser;
use Zlikavac32\QueryBuilder\ProfilerParser;
use Zlikavac32\QueryBuilder\SectionMap;

class ProfilerParserSpec extends ObjectBehavior
{

    public function let(Parser $parser): void
    {
        $this->beConstructedWith($parser);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProfilerParser::class);
    }

    public function it_should_collect_traces(Parser $parser): void
    {
        $firstSectionMap = new SectionMap();
        $secondSectionMap = new SectionMap();

        $expcetion = new LogicException();

        $parser->parse('foo', [1])->willReturn($firstSectionMap);
        $parser->parse('bar', [2])->willThrow($expcetion);
        $parser->parse('baz', [3])->willReturn($secondSectionMap);

        $this->parse('foo', [1])->shouldReturn($firstSectionMap);
        $this->shouldThrow($expcetion)->duringParse('bar', [2]);
        $this->parse('baz', [3])->shouldReturn($secondSectionMap);

        $traces = $this->traces();
        $traces->shouldHaveCount(3);

        $traces[0]['sql']->shouldBe('foo');
        $traces[0]['duration']->shouldBeFloat();
        $traces[0]['exception']->shouldBe(false);

        $traces[1]['sql']->shouldBe('bar');
        $traces[1]['duration']->shouldBeFloat();
        $traces[1]['exception']->shouldBe(true);

        $traces[2]['sql']->shouldBe('baz');
        $traces[2]['duration']->shouldBeFloat();
        $traces[2]['exception']->shouldBe(false);
    }

    public function it_should_reset_traces(Parser $parser): void
    {
        $firstSectionMap = new SectionMap();
        $secondSectionMap = new SectionMap();

        $parser->parse('foo', [1])->willReturn($firstSectionMap);
        $parser->parse('bar', [2])->willReturn($secondSectionMap);

        $this->parse('foo', [1])->shouldReturn($firstSectionMap);
        $this->parse('bar', [2])->shouldReturn($secondSectionMap);

        $this->traces()->shouldHaveCount(2);

        $this->reset();

        $this->traces()->shouldHaveCount(0);

        $this->parse('foo', [1])->shouldReturn($firstSectionMap);

        $this->traces()->shouldHaveCount(1);

        $this->reset();

        $this->traces()->shouldHaveCount(0);
    }
}
